<?php

namespace App\Validators;

use App\Constants\EstimationStatus;
use App\Constants\UserRoles;
use App\Models\Task;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class TaskValidator
{
  public static function validateStaffIsAssignedToTask(
    $task,
    ?string $userId = null,
    ?string $message = null
  ): void {
    $userId = $userId ?? auth()->id();
    $message = $message ?? 'Only assigned staff can do this action';

    if ($task->assigned_to !== $userId) {
      throw new Exception($message, Response::HTTP_FORBIDDEN);
    }
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
