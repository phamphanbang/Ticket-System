<?php 

namespace App\Validators;

use App\Constants\TicketStatus;
use App\Exceptions\InvalidTicketAssignmentException;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TicketValidator 
{
  public static function checkTicketExists($ticket)
  {
    if ($ticket) return;
    throw new ModelNotFoundException(__('messages.model_not_found', ['model' => 'Ticket']));
  }

  public static function checkTicketStatus(Ticket $ticket, $status)
  {
    if ($ticket->status->value == $status) return;
    $errorMessage = "";
    switch ($status) {
      case TicketStatus::New->value:
        $errorMessage = __('error.ticket_assigned_not_new');
        break;

      case TicketStatus::InProgress->value:
        $errorMessage = __('error.ticket_not_in_progress');
        break;

      default:

        break;
    }
    throw new InvalidTicketAssignmentException($errorMessage);
  }

  public static function checkStaffIsAssignedToTicket(Ticket $ticket, $staff_id)
  {
    if ($ticket->assign_to == $staff_id) return;
    throw new InvalidTicketAssignmentException(__('error.ticket_assigned_not_your_ticket'));
  }
  public static function checkTicketNotAssignedToTheSameStaff(Ticket $ticket, $staff_id)
  {
    if ($ticket->assign_to != $staff_id) return;
    throw new InvalidTicketAssignmentException(__('error.ticket_assigned_same_staff'));
  }
}