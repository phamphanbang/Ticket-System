<?php

namespace App\Services;

use App\Constants\ExternalStatus;
use App\Constants\InternalStatus;
use App\Mail\ClientTicketCreated;
use App\Models\Ticket;
use App\Models\TicketAuditLog;
use App\Validators\TicketValidator;
use Illuminate\Support\Facades\Mail;

class TicketService
{
  public function __construct(
    protected ClientService $clientService,
  ) {
    // Constructor to inject ClientService dependency

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
