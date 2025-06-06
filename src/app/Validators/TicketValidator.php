<?php

namespace App\Validators;

use App\Constants\TicketStatus;
use App\Constants\UserRoles;
use App\Exceptions\InvalidTicketAssignmentException;
use App\Models\Ticket;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TicketValidator
{
  public static function checkTicketExists($ticket)
  {
    if ($ticket) return;
    throw new ModelNotFoundException(__('messages.model_not_found', ['model' => 'Ticket']));
  }

  public static function checkTicketBelongsToHolderOrStaff($ticket, $message)
  {
    $user = auth()->user();
    if ($ticket->holder_id !== $user->id && $ticket->staff_id !== $user->id) {
      throw new Exception(
        $message,
        Response::HTTP_FORBIDDEN
      );
    }
  }

  public static function checkTicketBelongsToHolder($ticket, $message)
  {
    $user = auth()->user();
    if ($ticket->holder_id !== $user->id) {
      throw new Exception(
        $message,
        Response::HTTP_FORBIDDEN
      );
    }
  }

  public static function checkUserAssignToThemselves($staffId, $message)
  {
    $user = auth()->user();
    if ($user->id == $staffId) {
      throw new Exception(
        $message,
        Response::HTTP_FORBIDDEN
      );
    }
  }

  public static function checkTicketIsCompleteOrClose($ticket, $message)
  {
    if (
      $ticket->status == TicketStatus::COMPLETE->value ||
      $ticket->status == TicketStatus::FORCE_CLOSED->value
    ) {
      throw new Exception(
        $message,
        Response::HTTP_FORBIDDEN
      );
    }
  }

  public static function checkTicketCanBeDeleted($ticket, $message)
  {
    if (
      $ticket->status !== TicketStatus::COMPLETE->value ||
      $ticket->status !== TicketStatus::FORCE_CLOSED->value
    ) {
      throw new Exception(
        $message,
        Response::HTTP_FORBIDDEN
      );
    }
  }

    public static function checkAuthorization(Ticket $ticket)
  {
    TicketValidator::checkTicketExists($ticket);
    $user = Auth::user();
    $isAuthorized = $ticket->logs()
      ->where(function ($query) use ($user) {
        $query->where('holder_id', $user->id)
          ->orWhere('staff_id', $user->id);
      })
      ->exists();

    if (!$isAuthorized && $user->role !== UserRoles::ADMIN->value) {
      throw new Exception(
        'You are not authorized to view this comment',
        Response::HTTP_FORBIDDEN
      );
    }
  }
}
