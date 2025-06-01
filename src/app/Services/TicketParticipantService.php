<?php

namespace App\Services;

use App\Http\Resources\TicketParticipantResource;
use App\Mail\TicketParticipantAdded;
use App\Mail\TicketParticipantRemoved;
use App\Models\Ticket;
use App\Models\TicketParticipant;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\DB;

class TicketParticipantService
{

  public function getTicketParticipants(string $ticketId)
  {
    return TicketParticipant::where('ticket_id', $ticketId)
      ->with(['user', 'invitedBy'])
      ->get()->map(function ($participant) {
        return new TicketParticipantResource($participant);
      });
  }

  /**
   * Add a participant to a ticket.
   *
   * @param string $ticketId
   * @param string $userId
   * @param string $invitedByUserId
   * @param string $role
   * @return TicketParticipantResource
   * @throws \Exception
   */
  public function addParticipant(
    string $ticketId,
    string $userId,
    string $invitedByUserId,
    string $role
  ) {
    if (!in_array($role, TicketParticipant::ROLES)) {
      throw new \Exception('Invalid role specified', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    // Check if user is already a participant
    $existingParticipant = TicketParticipant::where('ticket_id', $ticketId)
      ->where('user_id', $userId)
      ->first();

    if ($existingParticipant) {
      if ($existingParticipant->isActive()) {
        throw new \Exception('User is already an active participant in this ticket', Response::HTTP_CONFLICT);
      }
      // If user left before, reactivate their participation
      $existingParticipant->update([
        'left_at' => null,
        'role_in_ticket' => $role,
        'invited_by_user_id' => $invitedByUserId,
      ]);

      $participant = $existingParticipant->load(['user', 'invitedBy']);
    } else {
      $participant = TicketParticipant::create([
        'ticket_id' => $ticketId,
        'user_id' => $userId,
        'invited_by_user_id' => $invitedByUserId,
        'role_in_ticket' => $role,
        'joined_at' => now(),
      ])->load(['user', 'invitedBy']);
    }

    // Send email notification
    Mail::to($participant->user->email)
      ->queue(new TicketParticipantAdded($participant));

    return new TicketParticipantResource($participant);
  }

  /**
   * Remove a participant from a ticket.
   *
   * @param string $participantId
   * @return bool
   */
  public function removeParticipant(string $participantId, string $removedByUserId): bool
  {
    $participant = TicketParticipant::findOrFail($participantId);
    $removedByUser = User::findOrFail($removedByUserId);

    // Admin can override all rules
    if (!$removedByUser->hasRole('admin')) {
      // Check if ticket is closed
      if ($participant->ticket->status === 'closed') {
        throw new \Exception('Cannot remove participants from a closed ticket', Response::HTTP_FORBIDDEN);
      }

      // Only leaders can remove participants
      if (!$removedByUser->hasRole('leader')) {
        throw new \Exception('Only leaders can remove participants', Response::HTTP_FORBIDDEN);
      }

      // Cannot remove last remaining leader
      if ($participant->role_in_ticket === 'leader') {
        $leaderCount = TicketParticipant::where('ticket_id', $participant->ticket_id)
          ->where('role_in_ticket', 'leader')
          ->whereNull('left_at')
          ->count();

        if ($leaderCount <= 1) {
          throw new \Exception('Cannot remove the last remaining leader', Response::HTTP_FORBIDDEN);
        }
      }
    }

    $result = $participant->update([
      'left_at' => now(),
    ]);

    if ($result) {
      // Send email notification
      Mail::to($participant->user->email)
        ->queue(new TicketParticipantRemoved($participant, $removedByUserId));
    }

    return $result;
  }
}
