<?php

namespace App\Services;

use App\Constants\AuditActions;
use App\Constants\PaginateConstant;
use App\Constants\TicketStatus;
use App\Constants\UserRoles;
use App\Events\AuditLogDeleted;
use App\Events\AuditLogged;
use App\Events\TicketUpdated;
use App\Jobs\FetchInfoCommandJob;
use App\Mail\ClientTicketCreated;
use App\Models\Ticket;
use App\Models\TicketAuditLog;
use App\Models\TicketEmail;
use App\Traits\HasAuditLog;
use App\Validators\TicketValidator;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;
use App\Jobs\NotifyStaffHasBeenAssigned;
use App\Jobs\NotifyTicketHasBeenCompleted;
use App\Mail\ClientTicketCompleted;
use App\Models\User;
use Illuminate\Support\Facades\View;

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
    $user = Auth::user();

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

    if (isset($filters["status"])) {
      $query->where('status', $filters['status']);
      if ($filters['status'] !== TicketStatus::ARCHIVED->value) {
        $query->where('status', '!=', TicketStatus::ARCHIVED->value);
      }
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

  public function store(array $data, bool $shouldSendEmail = true)
  {
    $client = $this->clientService->createClient([
      'name' => explode('@', $data['client_email'])[0],
      'email' => $data['client_email']
    ]);
    $user = Auth::user();
    if (!$user) {
      $user = User::where('email', env('ADMIN_EMAIL'))->first();
    }
    $data['client_id'] = $client->id;
    $data['status'] = TicketStatus::NEW->value;
    $data['holder_id'] = $user->id;
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
    if ($shouldSendEmail) {
      $mail = TicketEmail::create([
        'from_email' => env('MAIL_FROM_ADDRESS'),
        'from_name' => $user->name,
        'to_email' => $ticket->client->email,
        'body' => View::make('mails.clients.admin_create_new_ticket', ['ticket' => $ticket])->render(),
        'subject' => '[ESReport] ' . $ticket->title,
        'type' => 'reply',
        'ticket_id' => $ticket->id,
        'received_at' => now(),
      ]);
      Mail::to($ticket->client->email)
        ->queue(new ClientTicketCreated($ticket, $mail));
      FetchInfoCommandJob::dispatch($mail->id);
    }

    return $ticket;
  }

  public function update(string $id, array $data): Ticket
  {
    $ticket = Ticket::where('id', $id)->first();

    TicketValidator::checkTicketExists($ticket);
    TicketValidator::checkTicketIsArchived(
      $ticket,
      'This ticket is closed to edit'
    );

    if (isset($data['status'])) {
      TicketValidator::checkTicketIsComplete(
        $ticket,
        $data['status'],
        'This ticket can only be archived'
      );
      TicketValidator::checkTicketIsReadyForArchived(
        $ticket,
        $data['status'],
        'This ticket is not ready to archived'
      );
    }
    TicketValidator::checkTicketBelongsToHolderOrStaff(
      $ticket,
      'You are not authorized to update this ticket'
    );
    $log = null;
    $ticket = DB::transaction(function () use ($ticket, $data, &$log) {
      $oldStatus = $ticket->status;
      $oldStaffId = $ticket->staff_id;
      $ticket->update($data);

      if (isset($data['status']) && $data['status'] !== $oldStatus) {
        $log = $this->handleTicketStatusChange($ticket, $data['status']);
        if ($data['status'] == TicketStatus::COMPLETE->value) {
          NotifyTicketHasBeenCompleted::dispatch($ticket);
          Mail::to($ticket->client->email)
            ->queue(new ClientTicketCompleted($ticket));
        }
      }

      if (isset($data['staff_id']) && $data['staff_id'] !== $oldStaffId) {
        TicketValidator::checkUserAssignToThemselves(
          $data['staff_id'],
          'You cannot assign to yourself'
        );
        $log = $this->handleTicketStatusChange($ticket, TicketStatus::ASSIGNED->value);
        NotifyStaffHasBeenAssigned::dispatch($ticket);
      }

      return $ticket;
    });
    event(new TicketUpdated($ticket));
    if ($log) {
      event(new AuditLogged($log));
    }

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
    $user = Auth::user();
    $query = $ticket->logs()
      ->with(['staff', 'holder'])
      ->orderBy('created_at', 'desc');
    if ($user->role == UserRoles::ADMIN->value) {
      $query->withTrashed();
    }
    // else {
    //   $query->where(function ($query) use ($user) {
    //     $query->where(function ($q) use ($user) {
    //       $q->where('staff_id', $user->id)
    //         ->orWhere('holder_id', $user->id);
    //     });
    //     $query->whereNull('deleted_at');
    //   });
    // }
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

    $user = Auth::user();

    if ($user->role !== UserRoles::ADMIN->value && $user->id !== $log->holder_id) {
      throw new Exception(
        'You are not authorized to delete this log',
        Response::HTTP_FORBIDDEN
      );
    }
    event(new AuditLogDeleted($log));
    $log->delete();
  }

  public function handleTicketStatusChange(
    Ticket $ticket,
    string $newStatus,
    string $action = AuditActions::STATUS_CHANGED->value
  ): TicketAuditLog {
    $latestAuditLog = $ticket->logs()
      ->whereNull('end_at')
      ->orderBy('created_at', 'asc')
      ->first();
    $log = null;
    if ($latestAuditLog) {
      $latestAuditLog->update([
        'action' => $action,
        'end_at' => now(),
        'to_status' => $newStatus,
      ]);
      $log = $latestAuditLog;
    }

    if ($newStatus == TicketStatus::COMPLETE->value || $newStatus == TicketStatus::ARCHIVED->value) {
      return $log;
    }

    $log = TicketAuditLog::create([
      'ticket_id' => $ticket->id,
      'action' => AuditActions::PENDING->value,
      'status' => $newStatus,
      'to_status' => null,
      'holder_id' => $ticket->holder_id,
      'staff_id' => $ticket->staff_id,
      'start_at' => now(),
      'end_at' => null,
    ]);

    return $log;
  }
}
