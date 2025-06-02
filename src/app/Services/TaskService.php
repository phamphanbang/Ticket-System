<?php

namespace App\Services;

use App\Constants\EstimationStatus;
use App\Constants\InternalStatus;
use App\Mail\TaskAssigned;
use App\Mail\TaskUnassigned;
use App\Models\Task;
use App\Models\TaskAuditLog;
use App\Models\Ticket;
use App\Models\TicketAuditLog;
use App\Models\User;
use App\Traits\HasAuditLog;
use Illuminate\Pagination\LengthAwarePaginator;
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

    $perPage = $filters['per_page'] ?? 15;
    $page = $filters['page'] ?? 1;

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
    if(isset($data['assigned_to']) && $data['assigned_to'] !== null) {
      // Check if assigned user is in participants list
      $ticket = Ticket::findOrFail($data['ticket_id']);
      $participants = $ticket->participants()->pluck('user_id')->toArray();
      
      if (!in_array($data['assigned_to'], $participants)) {
        throw new \Exception('Assigned user must be a participant of the ticket',Response::HTTP_BAD_REQUEST);
      }
      
      $data['estimation_status'] = EstimationStatus::ASSIGNED->value;
    }
    $task = Task::create($data)->fresh();

    if(isset($data['assigned_to']) && $data['assigned_to'] !== null){
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
    $ticket = $task->ticket;
    // Get leader from ticket participants
    $leader = $ticket->participants()
        ->where('role_in_ticket', 'leader')
        ->first();

    if (!($leader && $leader->user_id === $user->id) && 
        !$user->hasRole('admin') && 
        $task->assigned_to !== $user->id) {
        throw new \Exception('You are not authorized to update this task', Response::HTTP_FORBIDDEN);
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
    if (!$staff->hasRole('staff')) {
        throw new \Exception('User must have staff role to be assigned to a task', Response::HTTP_BAD_REQUEST);
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

    Mail::to($staff->email)->queue(new TaskAssigned($task));

    if ($oldStaff) {
      Mail::to($oldStaff->email)->queue(new TaskUnassigned($task)); 
    }

    return $task->fresh();
  }

  public function readyToReview(string $id): Task
  {
    $task = Task::findOrFail($id);

    if (
      ($task->estimation_status === EstimationStatus::ASSIGNED->value || $task->estimation_status === EstimationStatus::NEEDS_REVISION->value) &&
      $task->assigned_to &&
      $task->estimated_time
    ) {
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
    }

    return $task->fresh();
  }

  public function notifyLeaderForReview(string $ticketId): void
  {
    $ticket = Ticket::with('tasks')->findOrFail($ticketId);
    
    // Check if all tasks are assigned and ready for review
    $allTasksReady = $ticket->tasks->every(function ($task) {
      return $task->estimation_status === EstimationStatus::READY_FOR_REVIEW->value 
        && $task->assigned_to !== null;
    });

    if ($allTasksReady) {
      $leader = $ticket->leader;
      
      if ($leader && $leader->email) {
        $oldInternalStatus = $ticket->internal_status;
        $ticket->update([
          'internal_status' => InternalStatus::AWAITING_ESTIMATION_APPROVAL->value
        ]);

        $this->createAuditLog(
          $ticket->id,
          'status',
          ['internal_status' => $oldInternalStatus],
          ['internal_status' => $ticket->internal_status],
          'All tasks ready for estimation review'
        );
      }
    }
  }

  /**
   * Change task estimation status to need revisions and notify assigned staff
   * 
   * @param string $taskId
   * @param string $revisionReason
   * @return Task
   */
  public function markEstimateNeedsRevision(string $taskId, string $revisionReason): Task
  {
    $task = Task::findOrFail($taskId);
    
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

  /**
   * Change task estimation status to finalized and notify assigned staff
   * 
   * @param string $taskId
   * @return Task
   */
  public function markEstimateApproved(string $taskId): Task
  {
    $task = Task::findOrFail($taskId);
    
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

    // Notify assigned staff member
    if ($task->assignedTo && $task->assignedTo->email) {
      // Mail::to($task->assignedTo->email)
      //   ->queue(new TaskEstimateApproved($task));
    }

    return $task->fresh();
  }
  public function destroy(string $id): bool
  {
    $task = Task::findOrFail($id);
    return $task->delete();
  }
}
