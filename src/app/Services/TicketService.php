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

  // public function update($data, $id)
  // {
  //   $ticket = Ticket::where('id', $id)->first();

  //   TicketValidator::checkTicketExists($ticket);
  //   TicketValidator::checkTicketStatus($ticket, TicketStatus::New->value);

  //   $oldTitle = $ticket->title;
  //   $oldDescription = $ticket->description;
  //   $oldAssignId = $ticket->assign_to;
  //   $newAssignId = $data['assign_to'] ?? null;

  //   $ticket->update($data);

  //   $oldUser = $oldAssignId ? User::where('id',$oldAssignId)->first() : null;
  //   $newUser = $newAssignId ? User::where('id',$newAssignId)->first() : null;

  //   if (is_null($oldAssignId) && $newUser) {
  //     Mail::to($newUser->email)
  //       ->queue(new StaffAssignedToNewTicket($ticket));
  //   }

  //   if ($oldUser && is_null($newAssignId)) {
  //     Mail::to($oldUser->email)->queue(new StaffUnassignedToTicket($ticket, $oldUser));
  //   }
  //   if ($oldUser && $newUser && $oldAssignId !== $newAssignId) {
  //     Mail::to($newUser->email)->queue(new StaffAssignedToNewTicket($ticket));
  //     Mail::to($oldUser->email)->queue(new StaffUnassignedToTicket($ticket, $oldUser));
  //   }
  //   if ($oldAssignId !== $newAssignId || $oldTitle !== $ticket->title || $oldDescription !== $ticket->description) {
  //     Mail::to($ticket->client->email)->queue(new ClientAdminUpdateTicket($ticket));
  //   }
  //   return $ticket;
  // }

  // public function staffConfirmTicket($data, $id)
  // {
  //   $user = auth()->user();
  //   $ticket = Ticket::where('id', $id)->first();

  //   TicketValidator::checkTicketExists($ticket);
  //   TicketValidator::checkTicketStatus($ticket, TicketStatus::New->value);
  //   TicketValidator::checkStaffIsAssignedToTicket($ticket, $user->id);

  //   $ticket->update($data);

  //   Mail::to($ticket->client->email)->queue(new ClientTicketIsConfirmed($ticket));

  //   return $ticket;
  // }

  // public function staffResolveTicket($data, $id)
  // {
  //   $user = auth()->user();
  //   $ticket = Ticket::where('id', $id)->first();

  //   TicketValidator::checkTicketExists($ticket);
  //   TicketValidator::checkTicketStatus($ticket, TicketStatus::InProgress->value);
  //   TicketValidator::checkStaffIsAssignedToTicket($ticket, $user->id);

  //   $ticket->update($data);

  //   Mail::to($ticket->client->email)->queue(new ClientTicketIsResolved($ticket));

  //   return $ticket;
  // }

  // public function staffDelayTicket($data, $id)
  // {
  //   $user = auth()->user();
  //   $ticket = Ticket::where('id', $id)->first();

  //   TicketValidator::checkTicketExists($ticket);
  //   TicketValidator::checkTicketStatus($ticket, TicketStatus::InProgress->value);
  //   TicketValidator::checkStaffIsAssignedToTicket($ticket, $user->id);

  //   $ticket->update($data);

  //   Mail::to($ticket->client->email)->queue(new ClientStaffDelayTicket($ticket));

  //   return $ticket;
  // }

  // public function clientRejectTicket($data, $id)
  // {
  //   $ticket = Ticket::where('id', $id)->first();
  //   TicketValidator::checkTicketExists($ticket);
  //   TicketValidator::checkTicketStatus($ticket, TicketStatus::Resolved->value);

  //   $ticket->update($data);

  //   Mail::to($ticket->assignTo->email)->queue(new StaffClientRejectTicket($ticket));

  //   return $ticket;
  // }

  // public function closeTicket($data, $id)
  // {
  //   $ticket = Ticket::where('id', $id)->first();
  //   $user = auth()->user();

  //   TicketValidator::checkTicketExists($ticket);
  //   if (!$user) {
  //     TicketValidator::checkTicketStatus($ticket, TicketStatus::Resolved->value);
  //   }
  //   if ($user && $user->isStaff()) {
  //     throw new InvalidTicketAssignmentException(__('error.you_are_not_admin'));
  //   }
  //   $ticket->update($data);
  //   if ($ticket->assign_to) {
  //     Mail::to($ticket->assignTo->email)->queue(new StaffTicketIsClosed($ticket));
  //   }

  //   if ($user) {
  //     Mail::to($ticket->client->email)->queue(new ClientTicketIsClosed($ticket));
  //   }

  //   return $ticket;
  // }

  public function getTicketById($id)
  {
    $ticket = Ticket::where('id', $id)->first();
    TicketValidator::checkTicketExists($ticket);

    return $ticket->load(['client']);
  }

  // public function createTicketFromMail($data, Client $ticket_client)
  // {
  //   $ticket = Ticket::create([
  //     'subject' => $data['subject'],
  //     'description' => $data['body'],
  //     'client_id' => $ticket_client->id,
  //     'internal_status' => InternalStatus::NEW->value,
  //     'external_status' => ExternalStatus::RECEIVED->value
  //   ]);

  //   return $ticket->load(['client']);
  // }
}
