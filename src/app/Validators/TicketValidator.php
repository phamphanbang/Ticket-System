<?php

namespace App\Validators;

use App\Constants\TicketStatus;
use App\Constants\UserRoles;
use App\Exceptions\InvalidTicketAssignmentException;
use App\Models\Ticket;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;

class TicketValidator
{
  public static function checkTicketExists($ticket)
  {
    if ($ticket) return;
    throw new ModelNotFoundException(__('messages.model_not_found', ['model' => 'Ticket']));
  }

  public static function checkTicketBelongsToHolderOrStaff($ticket,$message)
  {
    $user = auth()->user();
    if ($ticket->holder_id !== $user->id && $ticket->staff_id !== $user->id) {
      throw new Exception(
        $message,
        Response::HTTP_FORBIDDEN
      );
    }
  }

  public static function checkTicketBelongsToHolder($ticket,$message)
  {
    $user = auth()->user();
    if ($ticket->holder_id !== $user->id) {
      throw new Exception(
        $message,
        Response::HTTP_FORBIDDEN
      );
    }
  }
}
