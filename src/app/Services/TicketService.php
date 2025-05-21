<?php

namespace App\Services;

use App\Mail\ClientTicketCreated;
use App\Mail\StaffAssignedToNewTicket;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Mail;

class TicketService
{

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

  public function getTicketById($id)
  {
    $ticket = Ticket::find($id);
    if (!$ticket) {
      throw new ModelNotFoundException(__('messages.model_not_found', ['model' => 'Ticket']));
    }

    return $ticket->load(['assignTo']);
  }
}
