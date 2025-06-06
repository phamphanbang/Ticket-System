<?php

namespace App\Services;

use App\Constants\AuditActions;
use App\Constants\EstimationStatus;
use App\Constants\ExecutionStatus;
use App\Constants\ExternalStatus;
use App\Constants\InternalStatus;
use App\Constants\PaginateConstant;
use App\Constants\TaskPhase;
use App\Constants\TicketStatus;
use App\Constants\UserRoles;
use App\Mail\ClientTicketCreated;
use App\Mail\ClientTicketProcessing;
use App\Mail\TicketClosed;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\TicketAuditLog;
use App\Traits\HasAuditLog;
use App\Validators\TicketValidator;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
    $query = Ticket::query()->with(['client', 'holder', 'staff']);
    $user = auth()->user();
    if (isset($filters['search'])) {
      $query->where(function ($q) use ($filters) {
        $q->where('title', 'like', "%{$filters['search']}%")
          ->orWhere('description', 'like', "%{$filters['search']}%")
          ->orWhereHas('client', function ($q) use ($filters) {
            $q->where('name', 'like', "%{$filters['search']}%")
              ->orWhere('email', 'like', "%{$filters['search']}%");
          });
      });
    }

    if ($user->role == UserRoles::ADMIN->value) {
      $query->withTrashed();
    } else {
      $query->where(function ($query) use ($user) {
        $query->where(function ($q) use ($user) {
          $q->where('staff_id', $user->id)
            ->orWhere('holder_id', $user->id);
        })
          ->orWhere(function ($q) use ($user) {
            $q->where('holder_id', $user->id)
              ->whereNotNull('deleted_at');
          })
          ->orWhereHas('logs', function ($q) use ($user) {
            $q->where('staff_id', $user->id);
          });
      });

      $query->whereNull('deleted_at');
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
      'data' => $paginator->items(),
      'pagination' => [
        'page' => $paginator->currentPage(),
        'perPage' => $paginator->perPage(),
        'total' => $paginator->total(),
      ]
    ];
  }

  public function show(string $id): Ticket
  {
    $ticket = Ticket::where('id', $id)->first();
    TicketValidator::checkTicketExists($ticket);

    return $ticket->load(['client', 'staff', 'holder']);
  }

  public function store(array $data)
  {
    $client = $this->clientService->createClient([
      'name' => explode('@', $data['client_email'])[0],
      'email' => $data['client_email']
    ]);
    $data['client_id'] = $client->id;
    $data['status'] = TicketStatus::NEW->value;
    $data['holder_id'] = auth()->user()->id;
    $ticket = DB::transaction(function () use ($data) {
      $ticket = Ticket::create($data);

      TicketAuditLog::create([
        'ticket_id' => $ticket->id,
        'action' => AuditActions::CREATED->value,
        'status' => $ticket->status,
        'to_status' => null,
        'holder_id' => $ticket->holder_id,
        'staff_id' => $ticket->staff_id,
        'start_at' => now(),
        'end_at' => null,
      ]);

      return $ticket->fresh();
    });
    Mail::to($ticket->client->email)
      ->queue(new ClientTicketCreated($ticket));

    return $ticket;
  }

  public function update(string $id, array $data): Ticket
  {
    $ticket = Ticket::where('id', $id)->first();

    TicketValidator::checkTicketExists($ticket);
    TicketValidator::checkTicketBelongsToHolderOrStaff(
      $ticket,
      'You are not authorized to update this ticket'
    );

    $ticket = DB::transaction(function () use ($ticket, $data) {
      $oldStatus = $ticket->status;
      $oldStaffId = $ticket->staff_id;
      $ticket->update($data);

      if (isset($data['status']) && $data['status'] !== $oldStatus) {
        $this->handleTicketStatusChange($ticket, $data['status']);
      }

      if (isset($data['staff_id']) && $data['staff_id'] !== $oldStaffId) {
        TicketValidator::checkUserAssignToThemselves(
          $data['staff_id'],
          'You cannot assign to yourself'
        );
        $this->handleTicketStatusChange($ticket, TicketStatus::ASSIGNED->value);
      }

      return $ticket;
    });

    return $ticket->fresh();
  }

  public function delete(string $id): Ticket
  {
    $ticket = Ticket::findOrFail($id);

    TicketValidator::checkTicketBelongsToHolder(
      $ticket,
      'You are not authorized to delete this ticket'
    );

    $ticket = DB::transaction(function () use ($ticket) {
      $ticket->delete();

      $this->handleTicketStatusChange($ticket, TicketStatus::DELETED->value, AuditActions::DELETED->value);

      return $ticket;
    });

    return $ticket;
  }


  public function getLogs(string $id, array $filters = []): array
  {
    $ticket = Ticket::findOrFail($id);

    $query = $ticket->logs()
      ->with(['staff', 'holder'])
      ->orderBy('created_at', 'desc');

    $perPage = $filters['limit'] ?? PaginateConstant::DEFAULT_PER_PAGE->value;
    $page = $filters['page'] ?? PaginateConstant::DEFAULT_PAGE->value;

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

  public function deleteLog(string $id): void
  {
    $log = TicketAuditLog::findOrFail($id);

    $user = auth()->user();

    if ($user->role !== UserRoles::ADMIN->value && $user->id !== $log->holder_id) {
      throw new Exception(
        'You are not authorized to delete this log',
        Response::HTTP_FORBIDDEN
      );
    }

    $log->delete();
  }

  public function getAttachments(string $id): array
  {
    $ticket = Ticket::findOrFail($id);

    $attachments = $ticket->comments()
      ->with(['attachments'])
      ->get()
      ->pluck('attachments')
      ->flatten()
      ->map(function ($attachment) {
        return [
          'id' => $attachment->id,
          'file_name' => $attachment->file_name,
          'file_path' => $attachment->file_path,
          'file_size' => $attachment->file_size,
          'file_extension' => $attachment->file_extension,
          'content_type' => $attachment->content_type,
          'created_at' => $attachment->created_at
        ];
      })
      ->values()
      ->toArray();

    return $attachments;
  }

  public function handleTicketStatusChange(
    Ticket $ticket,
    string $newStatus,
    string $action = AuditActions::STATUS_CHANGED->value
  ): void {
    $latestAuditLog = $ticket->logs()
      ->whereNull('end_at')
      ->orderBy('created_at', 'asc')
      ->first();

    if ($latestAuditLog) {
      $latestAuditLog->update([
        'action' => $action,
        'end_at' => now(),
        'to_status' => $newStatus,
      ]);
    }

    TicketAuditLog::create([
      'ticket_id' => $ticket->id,
      'action' => AuditActions::PENDING->value,
      'status' => $newStatus,
      'to_status' => null,
      'holder_id' => $ticket->holder_id,
      'staff_id' => $ticket->staff_id,
      'start_at' => now(),
      'end_at' => null,
    ]);
  }
}
