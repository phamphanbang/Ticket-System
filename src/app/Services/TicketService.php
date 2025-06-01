<?php

namespace App\Services;

use App\Constants\ExternalStatus;
use App\Constants\InternalStatus;
use App\Mail\ClientTicketCreated;
use App\Models\Ticket;
use App\Models\TicketAuditLog;
use App\Validators\TicketValidator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Pagination\LengthAwarePaginator;

class TicketService
{
  public function __construct(
    protected ClientService $clientService,
  ) {
    // Constructor to inject ClientService dependency

  }

  public function index(array $filters = []): array
  {
    $query = Ticket::query()->with(['client', 'participants.user']);

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

    if (isset($filters['created_by'])) {
        $query->where('created_by', $filters['created_by']);
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

  public function store(array $data)
  {
    $client = $this->clientService->createClient([
      'name' => explode('@', $data['client_email'])[0],
      'email' => $data['client_email']
    ]);

    $data['client_id'] = $client->id;
    $data['internal_status'] = InternalStatus::NEW->value;
    $data['external_status'] = ExternalStatus::RECEIVED->value;

    $ticket = Ticket::create($data);

    // Create audit log for ticket creation
    TicketAuditLog::create([
      'ticket_id' => $ticket->id,
      'changed_by' => $data['created_by'] ?? auth()->id(),
      'field_changed' => 'ticket_created',
      'old_value' => null,
      'new_value' => json_encode([
        'title' => $ticket->title,
        'internal_status' => $ticket->internal_status,
        'external_status' => $ticket->external_status,
      ]),
      'change_type' => 'update',
      'reason' => 'Ticket created',
    ]);

    Mail::to($ticket->client->email)
      ->queue(new ClientTicketCreated($ticket));

    return $ticket;
  }

  public function getTicketById($id)
  {
    $ticket = Ticket::where('id', $id)->first();
    TicketValidator::checkTicketExists($ticket);

    return $ticket->load(['client']);
  }

}
