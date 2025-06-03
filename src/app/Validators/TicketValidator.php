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

  public static function validateUserIsLeader(
    $task,
    ?string $userId = null,
    ?string $message = null
  ): void {
    $userId = $userId ?? auth()->id();
    $message = $message ?? 'Only ticket leaders can do this action';

    $isLeader = $task->ticket->participants()
      ->where('user_id', $userId)
      ->where('role_in_ticket', UserRoles::LEADER->value)
      ->exists();

    if (!$isLeader) {
      throw new Exception($message, Response::HTTP_FORBIDDEN);
    }
  }
}