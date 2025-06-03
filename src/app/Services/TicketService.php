<?php

namespace App\Services;

use App\Constants\EstimationStatus;
use App\Constants\ExecutionStatus;
use App\Constants\ExternalStatus;
use App\Constants\InternalStatus;
use App\Constants\TaskPhase;
use App\Constants\UserRoles;
use App\Mail\ClientTicketAwaitingApproval;
use App\Mail\ClientTicketCreated;
use App\Mail\ClientTicketProcessing;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\TicketAuditLog;
use App\Traits\HasAuditLog;
use App\Validators\TicketValidator;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

class TicketService
{
  use HasAuditLog;

  public function __construct(
    protected ClientService $clientService,
  ) {
    // Constructor to inject ClientService dependency

  }

  public function index(array $filters = []): array
  {
    $query = Ticket::query()->with(['client', 'participants']);

    if (auth()->user()->hasRole(UserRoles::STAFF->value)) {
      $query->whereHas('participants', function ($q) {
        $q->where('user_id', auth()->id());
      });
    }

    if (isset($filters['search'])) {
      $query->where(function ($q) use ($filters) {
        $q->where('subject', 'like', "%{$filters['search']}%")
          ->orWhere('description', 'like', "%{$filters['search']}%")
          ->orWhereHas('client', function ($q) use ($filters) {
            $q->where('name', 'like', "%{$filters['search']}%")
              ->orWhere('email', 'like', "%{$filters['search']}%");
          });
      });
    }

    if (isset($filters['internal_status'])) {
      $query->where('internal_status', $filters['internal_status']);
    }

    if (isset($filters['external_status'])) {
      $query->where('external_status', $filters['external_status']);
    }

    // if (isset($filters['created_by'])) {
    //   $query->where('created_by', $filters['created_by']);
    // }

    if (isset($filters['sort_by'])) {
      $direction = $filters['sort_direction'] ?? 'desc';
      $query->orderBy($filters['sort_by'], $direction);
    } else {
      $query->latest();
    }

    $perPage = $filters['limit'] ?? 15;
    $page = $filters['page'] ?? 1;

    $paginator = $query->paginate($perPage, ['*'], 'page', $page);

    return [
      'data' => $paginator->items(),
      'pagination' => [
        'page' => $paginator->currentPage(),
        'perPage' => $paginator->perPage(),
        'total' => $paginator->total(),
      ]
    ];
  }

  public function store(array $data)
  {
    $user = auth()->user();
    if ($user && $user->hasRole(UserRoles::STAFF->value)) {
      throw new Exception('Only supporters and admins can create tickets', Response::HTTP_FORBIDDEN);
    }

    $client = $this->clientService->createClient([
      'name' => explode('@', $data['client_email'])[0],
      'email' => $data['client_email']
    ]);

    $data['client_id'] = $client->id;
    $data['internal_status'] = InternalStatus::NEW->value;
    $data['external_status'] = ExternalStatus::RECEIVED->value;

    $ticket = Ticket::create($data);

    $this->createAuditLog(
      $ticket->id,
      'ticket_created',
      [],
      [
        'title' => $ticket->title,
        'internal_status' => $ticket->internal_status,
        'external_status' => $ticket->external_status,
      ],
      'Ticket created'
    );

    Mail::to($ticket->client->email)
      ->queue(new ClientTicketCreated($ticket));

    return $ticket;
  }

  public function update(string $id, array $data): Ticket
  {

    $ticket = Ticket::findOrFail($id);

    $user = auth()->user();

    // Check if user is leader, supporter or admin in ticket participants
    if (!$user->hasRole(UserRoles::ADMIN->value)) {
      $isAuthorized = $ticket->participants()
        ->where('user_id', $user->id)
        ->whereIn('role_in_ticket', [UserRoles::LEADER->value, UserRoles::SUPPORTER->value])
        ->exists();

      if (!$isAuthorized) {
        throw new Exception('Only ticket leaders, supporters and admins can update tickets', Response::HTTP_FORBIDDEN);
      }
    }

    $oldData = [
      'subject' => $ticket->subject,
      'description' => $ticket->description,
      'client_email' => $ticket->client->email
    ];

    // Update client email if changed
    if (isset($data['client_email']) && $data['client_email'] !== $ticket->client->email) {
      $client = $this->clientService->createClient([
        'name' => explode('@', $data['client_email'])[0],
        'email' => $data['client_email']
      ]);
      $data['client_id'] = $client->id;
    }

    $ticket->update($data);

    $this->createAuditLog(
      $ticket->id,
      'ticket_updated',
      $oldData,
      [
        'title' => $ticket->title,
        'description' => $ticket->description,
        'priority' => $ticket->priority,
        'client_email' => $ticket->client->email
      ],
      'Ticket updated'
    );

    return $ticket->fresh();
  }

  public function notifyLeaderForReview(string $id): void
  {
    $task = Task::findOrFail($id);
    $ticket = $task->ticket;

    // Check if all tasks are assigned and ready for review
    $allTasksReady = $ticket->tasks->every(function ($task) {
      return $task->estimation_status === EstimationStatus::READY_FOR_REVIEW->value
        && $task->assigned_to !== null;
    });

    if (!$allTasksReady) return;

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

    $leaders = $ticket->participants()->where('role_in_ticket', UserRoles::LEADER->value)->get();

    foreach ($leaders as $leader) {
      if ($leader->email) {
        // Mail::to($leader->email)
        //   ->queue(new TaskReadyForReview($task));
      }
    }
  }

  public function checkAndUpdateInitialStatus($ticket_id): Ticket
  {
    $ticket = Ticket::findOrFail($ticket_id);

    if (
      $ticket->internal_status !== InternalStatus::NEW->value ||
      $ticket->external_status !== ExternalStatus::RECEIVED->value
    ) {
      return $ticket;
    }
    $oldInternalStatus = $ticket->internal_status;
    $oldExternalStatus = $ticket->external_status;

    $ticket->update([
      'internal_status' => InternalStatus::IN_ANALYSIS->value,
      'external_status' => ExternalStatus::PROCESSING->value
    ]);

    $this->createAuditLog(
      $ticket->id,
      'status',
      [
        'internal_status' => $oldInternalStatus,
        'external_status' => $oldExternalStatus
      ],
      [
        'internal_status' => $ticket->internal_status,
        'external_status' => $ticket->external_status
      ],
      'Initial ticket status updated to Processing'
    );

    Mail::to($ticket->client->email)
      ->queue(new ClientTicketProcessing($ticket));

    return $ticket;
  }


  public function changeToAwaitingClientApproval(string $ticket_id): Ticket
  {
    $ticket = Ticket::findOrFail($ticket_id);

    if ($ticket->internal_status !== InternalStatus::AWAITING_ESTIMATION_APPROVAL->value) {
      throw new Exception(
        'Ticket must be in Awaiting Estimation Approval status to proceed',
        Response::HTTP_BAD_REQUEST
      );
    }

    if (!auth()->user()->hasAnyRole([
      UserRoles::LEADER->value,
      UserRoles::ADMIN->value
    ])) {
      throw new Exception(
        'Only leaders can change ticket to Awaiting Client Approval status',
        Response::HTTP_FORBIDDEN
      );
    }

    $oldInternalStatus = $ticket->internal_status;
    $oldExternalStatus = $ticket->external_status;

    $ticket->update([
      'internal_status' => InternalStatus::AWAITING_CLIENT_APPROVAL->value,
      'external_status' => ExternalStatus::AWAITING_YOUR_APPROVAL->value
    ]);

    $this->createAuditLog(
      $ticket->id,
      'status',
      [
        'internal_status' => $oldInternalStatus,
        'external_status' => $oldExternalStatus
      ],
      [
        'internal_status' => $ticket->internal_status,
        'external_status' => $ticket->external_status
      ],
      'Changed to Awaiting Client Approval status'
    );

    Mail::to($ticket->client->email)->queue(new ClientTicketAwaitingApproval($ticket));

    return $ticket;
  }

  public function clientApprove(string $ticket_id): Ticket
  {
    $ticket = Ticket::findOrFail($ticket_id);

    if ($ticket->internal_status !== InternalStatus::AWAITING_CLIENT_APPROVAL->value) {
      throw new Exception(
        'Ticket must be in Awaiting Client Approval status to proceed',
        Response::HTTP_BAD_REQUEST
      );
    }

    $oldInternalStatus = $ticket->internal_status;
    $oldExternalStatus = $ticket->external_status;

    $ticket->update([
      'internal_status' => InternalStatus::IN_PROGRESS->value,
      'external_status' => ExternalStatus::PROCESSING->value
    ]);

    $this->createAuditLog(
      $ticket->id,
      'status',
      [
        'internal_status' => $oldInternalStatus,
        'external_status' => $oldExternalStatus
      ],
      [
        'internal_status' => $ticket->internal_status,
        'external_status' => $ticket->external_status
      ],
      'Client approved ticket estimation'
    );

    Mail::to($ticket->client->email)->send(new ClientTicketProcessing($ticket));

    return $ticket;
  }

  public function checkAndCompleteTicket(string $task_id): ?Ticket
  {
    $task = Task::findOrFail($task_id);
    $ticket = $task->ticket;

    // Get all tasks for this ticket
    $tasks = $ticket->tasks;

    // Check if all tasks are in execution phase and completed
    $allTasksCompleted = $tasks->every(function ($task) {
      return $task->phase === 'execution' &&
        $task->execution_status === 'completed';
    });

    if ($allTasksCompleted) {
      $oldInternalStatus = $ticket->internal_status;
      $oldExternalStatus = $ticket->external_status;

      $ticket->update([
        'internal_status' => InternalStatus::COMPLETED->value,
        'external_status' => ExternalStatus::COMPLETED->value
      ]);

      $this->createAuditLog(
        $ticket->id,
        'status',
        [
          'internal_status' => $oldInternalStatus,
          'external_status' => $oldExternalStatus
        ],
        [
          'internal_status' => $ticket->internal_status,
          'external_status' => $ticket->external_status
        ],
        'Ticket automatically marked as completed - all tasks finished'
      );

      return $ticket;
    }

    return null;
  }

  public function notifyLeaderForExecutionReview(string $id): void
  {
    $task = Task::findOrFail($id);
    $ticket = $task->ticket;

    // Check if all tasks are ready for execution review
    $allTasksReady = $ticket->tasks->every(function ($task) {
      return $task->phase === TaskPhase::EXECUTION->value &&
        $task->execution_status === ExecutionStatus::READY_FOR_REVIEW->value;
    });

    if (!$allTasksReady) {
      return;
    }

    $leaders = $ticket->participants()
      ->where('role_in_ticket', UserRoles::LEADER->value)
      ->get();

    foreach ($leaders as $leader) {
      if ($leader->email) {
        // Mail::to($leader->email)
        //   ->queue(new TaskReadyForExecutionReview($task));
      }
    }

    $oldInternalStatus = $ticket->internal_status;
    $ticket->update([
      'internal_status' => InternalStatus::UNDER_REVIEW->value
    ]);

    $this->createAuditLog(
      $ticket->id,
      'status',
      ['internal_status' => $oldInternalStatus],
      ['internal_status' => $ticket->internal_status],
      'All tasks ready for execution review'
    );
  }

  public function getTicketById($id)
  {
    $ticket = Ticket::where('id', $id)->first();
    TicketValidator::checkTicketExists($ticket);

    return $ticket->load(['client', 'participants']);
  }
}
