<?php 

namespace App\Services;

use App\Constants\TicketStatus;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TicketService {

  public function createTicket($data) {
    $ticket = Ticket::create($data);

    return $ticket->load(['assignTo','assignTo.role']);
  }

  public function getTicketById($id) {
    $ticket = Ticket::find($id);
    if (!$ticket) {
      throw new ModelNotFoundException(__('messages.model_not_found', ['model' => 'Ticket']));
    }

    return $ticket->load(['assignTo']);
  }

}