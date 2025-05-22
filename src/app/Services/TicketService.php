<?php

namespace App\Services;

use App\Constants\TicketStatus;
use App\Constants\UserRoles;
use App\Exceptions\InvalidTicketAssignmentException;
use App\Mail\ClientAdminAssignStaff;
use App\Mail\ClientTicketCreated;
use App\Mail\ClientTicketIsConfirmed;
use App\Mail\StaffAssignedToNewTicket;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Mail;

class TicketService
{
  public function getListTicket($request)
  {
    $user = auth()->user();
    $list = [];
    $query = Ticket::query();

    if ($user->role == UserRoles::STAFF->value) {
      $query = $query->where('assign_to', $user->id);
    }

    $statusList = TicketStatus::list();

    foreach ($statusList as $status) {
      $list[$status->column_label()] = $query->withStatus($status)->get();
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

  public function adminAssignTicket($data, $id)
  {
    $ticket = Ticket::where('id', $id)->first();
    $this->checkTicketExists($ticket);
    $this->checkTicketStatus($ticket, TicketStatus::New->value);
    $this->checkTicketNotAssignedToTheSameStaff($ticket, $data['assign_to']);

    $ticket->update($data);

    if ($ticket->assign_to) {
      Mail::to($ticket->assignTo->email)
        ->queue(new StaffAssignedToNewTicket($ticket));
    }

    Mail::to($ticket->client_email)
      ->queue(new ClientAdminAssignStaff($ticket));

    return $ticket;
  }

  public function staffConfirmTicket($data, $id)
  {
    $user = auth()->user();
    $ticket = Ticket::where('id', $id)->first();

    $this->checkTicketExists($ticket);
    $this->checkTicketStatus($ticket, TicketStatus::New->value);
    $this->checkAuthIsAssignedToTicket($ticket, $user->id);

    $ticket->update($data);

    Mail::to($ticket->client_email)->queue(new ClientTicketIsConfirmed($ticket));

    return $ticket;
  }

  public function getTicketById($id)
  {
    $ticket = Ticket::find($id);
    $this->checkTicketExists($ticket);

    return $ticket->load(['assignTo']);
  }

  public function checkTicketExists($ticket)
  {
    if ($ticket) return;
    throw new ModelNotFoundException(__('messages.model_not_found', ['model' => 'Ticket']));
  }

  public function checkTicketStatus($ticket, $status)
  {
    if ($ticket->status->value == $status) return;
    $errorMessage = "";
    switch ($status) {
      case TicketStatus::New->value:
        $errorMessage = __('error.ticket_assigned_not_new');
        break;

      default:
        break;
    }
    throw new InvalidTicketAssignmentException($errorMessage);
  }

  public function checkAuthIsAssignedToTicket($ticket, $staff_id)
  {
    if ($ticket->assign_to == $staff_id) return;
    throw new InvalidTicketAssignmentException(__('error.ticket_assigned_not_your_ticket'));
  }

  public function checkTicketNotAssignedToTheSameStaff($ticket, $staff_id)
  {
    if ($ticket->assign_to != $staff_id) return;
    throw new InvalidTicketAssignmentException(__('error.ticket_assigned_same_staff'));
  }
}
