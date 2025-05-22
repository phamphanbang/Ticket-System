<?php

namespace App\Services;

use App\Constants\TicketStatus;
use App\Constants\UserRoles;
use App\Exceptions\InvalidTicketAssignmentException;
use App\Mail\ClientAdminAssignStaff;
use App\Mail\ClientTicketCreated;
use App\Mail\StaffAssignedToNewTicket;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class TicketService
{
  public function getListTicket($request)
  {
    $user = auth()->user();
    $list = [];
    $query = Ticket::query();

    if ($user->role == UserRoles::STAFF->value) {
      $query = $query->where('assign_to',$user->id);
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

  public function adminAssignTicket($data,$id)
  {
    $ticket = Ticket::where('id', $id)->first();
    if (!$ticket) {
      throw new ModelNotFoundException(__('messages.model_not_found', ['model' => 'Ticket']));
    }
    if ($ticket->status->value != TicketStatus::New->value) {
      throw new InvalidTicketAssignmentException(__('error.ticket_assigned_not_new'));
    }

    if($data['assign_to'] == $ticket->assign_to) {
      throw new InvalidTicketAssignmentException(__('error.ticket_assigned_same_staff'));
    }

    $ticket->update($data);

    if ($ticket->assign_to) {
      Mail::to($ticket->assignTo->email)
        ->queue(new StaffAssignedToNewTicket($ticket));
    }

    Mail::to($ticket->client_email)
      ->queue(new ClientAdminAssignStaff($ticket));

    return $ticket;
  }

  public function getTicketById($id)
  {
    $ticket = Ticket::find($id);
    if (!$ticket) {
      throw new ModelNotFoundException(__('messages.model_not_found', ['model' => 'Ticket']));
    }

    return $ticket->load(['assignTo']);
  }
}
