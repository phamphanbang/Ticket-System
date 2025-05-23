<?php

namespace App\Services;

use App\Constants\TicketStatus;
use App\Constants\UserRoles;
use App\Exceptions\InvalidTicketAssignmentException;
use App\Mail\ClientAdminUpdateTicket;
use App\Mail\ClientStaffDelayTicket;
use App\Mail\ClientTicketCreated;
use App\Mail\ClientTicketIsConfirmed;
use App\Mail\ClientTicketIsResolved;
use App\Mail\StaffAssignedToNewTicket;
use App\Mail\StaffUnassignedToTicket;
use App\Models\Ticket;
use App\Models\User;
use App\Validators\TicketValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Mail;

class TicketService
{
  public function getListTicket($request)
  {
    $user = auth()->user();
    $list = [];
    $query = Ticket::query()->with('assignTo');

    if ($user->role == UserRoles::STAFF->value) {
      $query = $query->where('assign_to', $user->id);
    }

    $statusList = TicketStatus::list();

    foreach ($statusList as $status) {
      $tempQuery = (clone $query)->where('status', $status->value);
      $list[$status->column_label()] = $tempQuery->get();
    }

    return $list;
  }

  public function createTicket($data)
  {
    $ticket = Ticket::create($data);

    return $ticket->load(['assignTo', 'assignTo.role']);
  }

  public function adminCreateTicket($data)
  {
    $ticket = $this->createTicket($data);
    if ($ticket->assign_to) {
      Mail::to($ticket->assignTo->email)
        ->queue(new StaffAssignedToNewTicket($ticket));
    }

    Mail::to($ticket->client_email)
      ->queue(new ClientTicketCreated($ticket));

    return $ticket;
  }

  public function update($data, $id)
  {
    $ticket = Ticket::where('id', $id)->first();

    TicketValidator::checkTicketExists($ticket);
    TicketValidator::checkTicketStatus($ticket, TicketStatus::New->value);

    $oldTitle = $ticket->title;
    $oldDescription = $ticket->description;
    $oldAssignId = $ticket->assign_to;
    $newAssignId = $data['assign_to'] ?? null;

    $ticket->update($data);

    $oldUser = $oldAssignId ? User::find($oldAssignId) : null;
    $newUser = $newAssignId ? User::find($newAssignId) : null;

    if (is_null($oldAssignId) && $newUser) {
      Mail::to($newUser->email)
        ->queue(new StaffAssignedToNewTicket($ticket));
    }

    if ($oldUser && is_null($newAssignId)) {
      Mail::to($oldUser->email)->queue(new StaffUnassignedToTicket($ticket));
    }
    if ($oldUser && $newUser && $oldAssignId !== $newAssignId) {
      Mail::to($newUser->email)->queue(new StaffAssignedToNewTicket($ticket));
      Mail::to($oldUser->email)->queue(new StaffUnassignedToTicket($ticket));
    }
    if ($oldAssignId !== $newAssignId || $oldTitle !== $ticket->title || $oldDescription !== $ticket->description) {
      Mail::to($ticket->client_email)->queue(new ClientAdminUpdateTicket($ticket));
    }

    return $ticket;
  }

  public function staffConfirmTicket($data, $id)
  {
    $user = auth()->user();
    $ticket = Ticket::where('id', $id)->first();

    TicketValidator::checkTicketExists($ticket);
    TicketValidator::checkTicketStatus($ticket, TicketStatus::New->value);
    TicketValidator::checkStaffIsAssignedToTicket($ticket, $user->id);

    $ticket->update($data);

    Mail::to($ticket->client_email)->queue(new ClientTicketIsConfirmed($ticket));

    return $ticket;
  }

  public function staffResolveTicket($data, $id)
  {
    $user = auth()->user();
    $ticket = Ticket::where('id', $id)->first();

    TicketValidator::checkTicketExists($ticket);
    TicketValidator::checkTicketStatus($ticket, TicketStatus::InProgress->value);
    TicketValidator::checkStaffIsAssignedToTicket($ticket, $user->id);

    $ticket->update($data);

    Mail::to($ticket->client_email)->queue(new ClientTicketIsResolved($ticket));

    return $ticket;
  }

  public function staffDelayTicket($data,$id)
  {
    $user = auth()->user();
    $ticket = Ticket::where('id', $id)->first();

    TicketValidator::checkTicketExists($ticket);
    TicketValidator::checkTicketStatus($ticket, TicketStatus::InProgress->value);
    TicketValidator::checkStaffIsAssignedToTicket($ticket, $user->id);

    $ticket->update($data);

    Mail::to($ticket->client_email)->queue(new ClientStaffDelayTicket($ticket));
    
    return $ticket;
  }

  public function getTicketById($id)
  {
    $ticket = Ticket::find($id);
    TicketValidator::checkTicketExists($ticket);

    return $ticket->load(['assignTo']);
  }
}
