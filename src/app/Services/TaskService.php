<?php

namespace App\Services;

use App\Constants\EstimationStatus;
use App\Constants\ExecutionStatus;
use App\Constants\PaginateConstant;
use App\Constants\TaskPhase;
use App\Constants\UserRoles;
use App\Mail\TaskAssigned;
use App\Mail\TaskUnassigned;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use App\Traits\HasAuditLog;
use App\Validators\TaskValidator;
use Exception;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class TaskService
{
  use HasAuditLog;

  public function index(array $filters = [], $ticket_id = null): array
  {
    $query = Task::query()->with(['ticket', 'assignedUser']);
    $query->where('ticket_id', $ticket_id);


    if (isset($filters['phase'])) {
      $query->inPhase($filters['phase']);
    }

    if (isset($filters['estimation_status'])) {
      $query->withEstimationStatus($filters['estimation_status']);
    }

    if (isset($filters['execution_status'])) {
      $query->withExecutionStatus($filters['execution_status']);
    }

    if (isset($filters['assigned_to'])) {
      $query->where('assigned_to', $filters['assigned_to']);
    }

    if (isset($filters['sort_by'])) {
      $direction = $filters['sort_direction'] ?? 'desc';
      $query->orderBy($filters['sort_by'], $direction);
    } else {
      $query->latest();
    }

    $perPage = $filters['limit'] ?? PaginateConstant::DEFAULT_PER_PAGE->value;
    $page = $filters['page'] ?? PaginateConstant::DEFAULT_PAGE->value;

    $paginator = $query->paginate($perPage, ['*'], 'page', $page);

    return [
      'items' => $paginator->items(),
      'pagination' => [
        'page' => $paginator->currentPage(),
        'perPage' => $paginator->perPage(),
        'total' => $paginator->total(),
      ]
    ];
  }

  public function store(array $data): Task
  {
    if (isset($data['assigned_to']) && $data['assigned_to'] !== null) {
      // Check if assigned user is in participants list
      $ticket = Ticket::findOrFail($data['ticket_id']);
      $participants = $ticket->participants()->pluck('user_id')->toArray();

      if (!in_array($data['assigned_to'], $participants)) {
        throw new Exception('Assigned user must be a participant of the ticket', Response::HTTP_BAD_REQUEST);
      }

      $data['estimation_status'] = EstimationStatus::ASSIGNED->value;
    }
    $task = Task::create($data)->fresh();

    if (isset($data['assigned_to']) && $data['assigned_to'] !== null) {
      Mail::to($task->assignedUser->email)->queue(new TaskAssigned($task));
    }

    return $task;
  }

  public function show(string $id): Task
  {
    return Task::with(['ticket', 'assignedUser'])->findOrFail($id);
  }

  public function update(string $id, array $data): Task
  {
    $task = Task::findOrFail($id);
    $user = request()->user();
    // Get leader from ticket participants
    $leader = $task->ticket->participants()
      ->where('role_in_ticket', UserRoles::LEADER->value)
      ->first();

    if (
      !($leader && $leader->user_id === $user->id) &&
      !$user->hasRole(UserRoles::ADMIN->value) &&
      $task->assigned_to !== $user->id
    ) {
      throw new Exception('You are not authorized to update this task', Response::HTTP_FORBIDDEN);
    }
    $task = Task::findOrFail($id);
    $task->update($data);
    $this->createAuditLog(
      $task->id,
      'task_updated',
      $task->getOriginal(),
      $task->getChanges(),
      'Task updated'
    );
    return $task->fresh();
  }

  public function assignStaff(string $id, string $staffId): Task
  {
    $task = Task::findOrFail($id);

    $staff = User::findOrFail($staffId);
    if (!$staff->hasRole(UserRoles::STAFF->value)) {
      throw new Exception('User must have staff role to be assigned to a task', Response::HTTP_BAD_REQUEST);
    }

    $oldStaffId = $task->assigned_to;

    // Skip notifications if same staff being reassigned
    if ($oldStaffId === $staffId) {
      return $task;
    }

    $oldStaff = $oldStaffId ? User::find($oldStaffId) : null;

    $task->update([
      'assigned_to' => $staffId,
      'estimation_status' => EstimationStatus::ASSIGNED->value
    ]);
    $this->createAuditLog(
      $task->id,
      'staff_assignment',
      [
        'assigned_to' => $oldStaffId,
        'estimation_status' => $task->getOriginal('estimation_status')
      ],
      [
        'assigned_to' => $staffId,
        'estimation_status' => $task->estimation_status
      ],
      $oldStaffId ? 'Task reassigned to new staff' : 'Task assigned to staff'
    );
    //Mail::to($staff->email)->queue(new TaskAssigned($task));

    if ($oldStaff) {
      Mail::to($oldStaff->email)->queue(new TaskUnassigned($task));
    }

    return $task->fresh();
  }

  public function readyToReview(string $id): Task
  {
    $task = Task::findOrFail($id);

    TaskValidator::validateStaffIsAssignedToTask($task);
    
    if (!in_array($task->estimation_status, [
      EstimationStatus::ASSIGNED->value,
      EstimationStatus::NEEDS_REVISION->value
    ])) {
      throw new Exception('Task must be in Assigned or Needs Revision status', Response::HTTP_BAD_REQUEST);
    }

    if (!$task->assigned_to) {
      throw new Exception('Task must be assigned to a staff member', Response::HTTP_BAD_REQUEST);
    }

    if (!$task->estimated_time) {
      throw new Exception('Task must have an estimated time', Response::HTTP_BAD_REQUEST);
    }

    $oldEstimationStatus = $task->estimation_status;
    $task->update([
      'estimation_status' => EstimationStatus::READY_FOR_REVIEW->value
    ]);

    $this->createAuditLog(
      $task->id,
      'estimation_status',
      ['estimation_status' => $oldEstimationStatus],
      ['estimation_status' => $task->estimation_status],
      'Task marked as ready for estimation review'
    );

    return $task->fresh();
  }

  public function markEstimateNeedsRevision(string $taskId, string $revisionReason): Task
  {
    $task = Task::with('ticket.participants')->findOrFail($taskId);

    TaskValidator::validateUserIsLeader(
      $task,
      null,
      'Only ticket leaders can mark tasks for revision.'
    );

    $oldEstimationStatus = $task->estimation_status;

    $task->update([
      'estimation_status' => EstimationStatus::NEEDS_REVISION->value
    ]);

    $this->createAuditLog(
      $task->id,
      'estimation_status',
      $oldEstimationStatus,
      $task->estimation_status,
      $revisionReason
    );

    // Notify assigned staff member
    if ($task->assignedTo && $task->assignedTo->email) {
      // Mail::to($task->assignedTo->email)
      //   ->queue(new TaskEstimateNeedsRevision($task, $revisionReason));
    }

    return $task->fresh();
  }

  public function markEstimateApproved(string $taskId): Task
  {
    $task = Task::with('ticket.participants')->findOrFail($taskId);

    TaskValidator::validateUserIsLeader(
      $task,
      null,
      'Only ticket leaders can approve task estimates.'
    );

    $oldEstimationStatus = $task->estimation_status;

    $task->update([
      'estimation_status' => EstimationStatus::FINALIZED->value
    ]);

    $this->createAuditLog(
      $task->id,
      'estimation_status',
      $oldEstimationStatus,
      $task->estimation_status,
      'Task estimation approved'
    );

    if ($task->assignedTo && $task->assignedTo->email) {
      // Mail::to($task->assignedTo->email)
      //   ->queue(new TaskEstimateApproved($task));
    }

    return $task->fresh();
  }

  public function changeTasksToExecution(string $ticketId)
  {
    $tasks = Task::where('ticket_id', $ticketId)->get();

    foreach ($tasks as $task) {
      $oldPhase = $task->phase;
      $oldExecutionStatus = $task->execution_status;

      $task->update([
        'phase' => TaskPhase::EXECUTION->value,
        'execution_status' => ExecutionStatus::NOT_STARTED->value
      ]);

      $this->createAuditLog(
        $task->id,
        'phase',
        $oldPhase,
        $task->phase,
        'Task moved to execution phase'
      );

      if ($task->assignedTo && $task->assignedTo->email) {
        // Mail::to($task->assignedTo->email)
        //   ->queue(new TaskMovedToExecution($task));
      }
    }

    return $tasks->fresh();
  }

  public function startExecution(string $id): Task
  {
    $task = Task::findOrFail($id);

    TaskValidator::validateStaffIsAssignedToTask($task);

    if (!in_array($task->execution_status, [
      ExecutionStatus::NOT_STARTED->value,
      ExecutionStatus::BLOCKED->value,
      ExecutionStatus::CHANGE_REQUESTED->value
    ])) {
      throw new Exception('Task must be in Not Started, Blocked or Change Requested status to begin execution', Response::HTTP_BAD_REQUEST);
    }

    $oldExecutionStatus = $task->execution_status;

    $task->update([
      'execution_status' => ExecutionStatus::IN_PROGRESS->value
    ]);
    $reason = $task->execution_status === ExecutionStatus::NOT_STARTED->value ?
      'Task execution started' :
      'Task unblocked and execution resumed';

    $this->createAuditLog(
      $task->id,
      'execution_status',
      ['execution_status' => $oldExecutionStatus],
      ['execution_status' => $task->execution_status],
      $reason
    );

    return $task->fresh();
  }

  public function blockTask(string $id, string $reason): Task
  {
    $task = Task::findOrFail($id);

    TaskValidator::validateStaffIsAssignedToTask(
      $task,
      null,
      'Only assigned staff can block this task'
    );

    if (!in_array($task->execution_status, [
      ExecutionStatus::NOT_STARTED->value,
      ExecutionStatus::IN_PROGRESS->value
    ])) {
      throw new Exception('Task must be Not Started or In Progress to be blocked', Response::HTTP_BAD_REQUEST);
    }

    $oldExecutionStatus = $task->execution_status;

    $task->update([
      'execution_status' => ExecutionStatus::BLOCKED->value
    ]);

    $this->createAuditLog(
      $task->id,
      'execution_status',
      ['execution_status' => $oldExecutionStatus],
      [
        'execution_status' => $task->execution_status,
        'reason' => $reason
      ],
      'Task blocked: ' . $reason
    );

    return $task->fresh();
  }

  public function changeRequest(string $id, array $data): Task
  {
    $task = Task::findOrFail($id);

    TaskValidator::validateUserIsLeader(
      $task,
      null,
      'Only ticket leader can request changes'
    );

    if ($task->execution_status !== ExecutionStatus::IN_PROGRESS->value) {
      throw new Exception('Task must be In Progress to request changes', Response::HTTP_BAD_REQUEST);
    }

    $oldData = [
      'description' => $task->description,
      'execution_status' => $task->execution_status
    ];

    $task->update([
      'description' => $data['description'],
      'execution_status' => ExecutionStatus::CHANGE_REQUESTED->value
    ]);

    $this->createAuditLog(
      $task->id,
      'change_request',
      $oldData,
      [
        'description' => $task->description,
        'execution_status' => $task->execution_status
      ],
      'Task description updated by leader'
    );

    return $task->fresh();
  }

  public function executionReadyToReview(string $id): Task
  {
    $task = Task::findOrFail($id);

    TaskValidator::validateStaffIsAssignedToTask(
      $task,
      null,
      'Only assigned staff can mark task as ready for review'
    );

    if ($task->execution_status !== ExecutionStatus::IN_PROGRESS->value) {
      throw new Exception('Task must be In Progress to mark as ready for review', Response::HTTP_BAD_REQUEST);
    }

    $oldExecutionStatus = $task->execution_status;

    $task->update([
      'execution_status' => ExecutionStatus::READY_FOR_REVIEW->value
    ]);

    $this->createAuditLog(
      $task->id,
      'execution_status_change',
      ['execution_status' => $oldExecutionStatus],
      ['execution_status' => $task->execution_status],
      'Task marked as ready for review by leader'
    );

    return $task->fresh();
  }

  public function completeExecution(string $id): Task
  {
    $task = Task::findOrFail($id);

    TaskValidator::validateUserIsLeader(
      $task,
      null,
      'Only ticket leaders can mark tasks as complete'
    );

    $oldExecutionStatus = $task->execution_status;

    $task->update([
      'execution_status' => ExecutionStatus::COMPLETED->value,
      'completed_at' => now()
    ]);

    $this->createAuditLog(
      $task->id,
      'execution_status_change',
      ['execution_status' => $oldExecutionStatus],
      ['execution_status' => $task->execution_status],
      'Task execution marked as complete by leader'
    );

    return $task->fresh();
  }
  public function destroy(string $id): bool
  {
    $task = Task::findOrFail($id);
    return $task->delete();
  }
}
