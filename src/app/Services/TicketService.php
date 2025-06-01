<?php

namespace App\Services;

use App\Constants\ExternalStatus;
use App\Constants\InternalStatus;
use App\Constants\PaginateConstant;
use App\Constants\TicketStatus;
use App\Constants\UserRoles;
use App\Exceptions\InvalidTicketAssignmentException;
use App\Http\Resources\TicketResource;
use App\Mail\ClientAdminUpdateTicket;
use App\Mail\ClientStaffDelayTicket;
use App\Mail\ClientTicketCreated;
use App\Mail\ClientTicketIsClosed;
use App\Mail\ClientTicketIsConfirmed;
use App\Mail\ClientTicketIsResolved;
use App\Mail\StaffAssignedToNewTicket;
use App\Mail\StaffClientRejectTicket;
use App\Mail\StaffTicketIsClosed;
use App\Mail\StaffUnassignedToTicket;
use App\Models\Client;
use App\Models\Ticket;
use App\Models\User;
use App\Validators\TicketValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
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
