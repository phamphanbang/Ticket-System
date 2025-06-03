<?php

namespace App\Services;

use App\Constants\UserRoles;
use App\Http\Resources\TicketParticipantResource;
use App\Models\TicketParticipant;
use App\Models\User;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class TicketParticipantService
{

  public function getTicketParticipants(string $ticketId)
  {
    return TicketParticipant::where('ticket_id', $ticketId)
      ->whereNull('left_at')
      ->with(['user', 'invitedBy'])
      ->get()->map(function ($participant) {
        return new TicketParticipantResource($participant);
      });
  }

  public function addParticipant(
    string $ticketId,
    array $userIds,
    string $invitedByUserId
  ) {

    // Check if user is already a participant
    $participants = collect();

    foreach ($userIds as $userId) {
      $existingParticipant = TicketParticipant::where('ticket_id', $ticketId)
        ->where('user_id', $userId)
        ->first();

      if ($existingParticipant) {
        if ($existingParticipant->isActive()) {
          // throw new \Exception("User $userId is already an active participant in this ticket", Response::HTTP_CONFLICT);
          continue;
        }
        // If user left before, reactivate their participation
        $existingParticipant->update([
          'left_at' => null,
          'invited_by_user_id' => $invitedByUserId,
        ]);

        $participants->push($existingParticipant->load(['user', 'invitedBy']));
      } else {
        $participant = TicketParticipant::create([
          'ticket_id' => $ticketId,
          'user_id' => $userId,
          'invited_by_user_id' => $invitedByUserId,
          'role_in_ticket' => User::find($userId)->roles->first()->name,
          'joined_at' => now(),
        ])->load(['user', 'invitedBy']);

        $participants->push($participant);
      }

      // Send email notification
      // Mail::to($participant->user->email)
      //   ->queue(new TicketParticipantAdded($participant));
    }

    return TicketParticipantResource::collection($participants);
  }

  public function removeParticipant(string $participantId, string $removedByUserId): bool
  {
    $participant = TicketParticipant::findOrFail($participantId);
    $removedByUser = User::findOrFail($removedByUserId);

    // Admin can override all rules
    if (!$removedByUser->hasRole(UserRoles::ADMIN->value)) {
      // Check if ticket is closed
      if ($participant->ticket->status === 'closed') {
        throw new Exception('Cannot remove participants from a closed ticket', Response::HTTP_FORBIDDEN);
      }

      // Only leaders can remove participants
      if (!$removedByUser->hasRole(UserRoles::LEADER->value) || !TicketParticipant::where('ticket_id', $participant->ticket_id)
        ->where('user_id', $removedByUser->id)
        ->where('role_in_ticket', UserRoles::LEADER->value)
        ->whereNull('left_at')
        ->exists()) {
        throw new Exception('Only assigned leaders can remove participants', Response::HTTP_FORBIDDEN);
      }

      // Cannot remove last remaining leader
      if ($participant->role_in_ticket === UserRoles::LEADER->value) {
        $leaderCount = TicketParticipant::where('ticket_id', $participant->ticket_id)
          ->where('role_in_ticket', UserRoles::LEADER->value)
          ->whereNull('left_at')
          ->count();

        if ($leaderCount <= 1) {
          throw new Exception('Cannot remove the last remaining leader', Response::HTTP_FORBIDDEN);
        }
      }
    }

    $result = $participant->update([
      'left_at' => now(),
    ]);

    if ($result) {
      // Send email notification
      // Mail::to($participant->user->email)->queue(new TicketParticipantRemoved($participant, $removedByUserId));
    }

    return $result;
  }

  public function removeParticipants(array $participantIds, string $removedByUserId, string $ticketId): array
  {
    $removedParticipants = [];
    $removedByUser = User::findOrFail($removedByUserId);
    // Group participants by ticket to validate leader counts
    $participantsByTicket = TicketParticipant::whereIn('id', $participantIds)
      ->where('ticket_id', $ticketId)
      ->get();

    foreach ($participantsByTicket as $ticketId => $participants) {
      // Pre-check leader count if any leaders are being removed
      if ($participants->role_in_ticket == UserRoles::LEADER->value) {
        $currentLeaderCount = TicketParticipant::where('ticket_id', $ticketId)
          ->where('role_in_ticket', UserRoles::LEADER->value)
          ->whereNull('left_at')
          ->count();

        $leadersToRemove = $participants->where('role_in_ticket', UserRoles::LEADER->value)->count();

        if ($currentLeaderCount <= $leadersToRemove && !$removedByUser->hasRole(UserRoles::ADMIN->value)) {
          throw new Exception('Cannot remove all leaders from ticket', Response::HTTP_FORBIDDEN);
        }
      }

      // Check if ticket is closed
      $ticket = $participants->first()->ticket;
      if ($ticket->status === 'closed' && !$removedByUser->hasRole(UserRoles::ADMIN->value)) {
        throw new Exception('Cannot remove participants from a closed ticket', Response::HTTP_FORBIDDEN);
      }
    }

    if (
      !$removedByUser->hasRole(UserRoles::ADMIN->value)
      && !$removedByUser->hasRole(UserRoles::LEADER->value)
    ) {
      throw new Exception('Only leaders can remove participants', Response::HTTP_FORBIDDEN);
    }

    foreach ($participantIds as $participantId) {
      try {
        $participant = TicketParticipant::findOrFail($participantId);
        $result = $participant->update([
          'left_at' => now(),
        ]);

        if ($result) {
          $removedParticipants[] = $participantId;
          // Send email notification
          // Mail::to($participant->user->email)->queue(new TicketParticipantRemoved($participant, $removedByUserId));
        }
      } catch (Exception $e) {
        Log::error("Failed to remove participant {$participantId}: " . $e->getMessage());
        continue;
      }
    }

    return $removedParticipants;
  }
}
