<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketParticipant;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\DB;

class TicketParticipantService
{
    /**
     * Get all participants for a specific ticket.
     *
     * @param string $ticketId
     * @return Collection
     */
    public function getTicketParticipants(string $ticketId): Collection
    {
        return TicketParticipant::where('ticket_id', $ticketId)
            ->with(['user', 'invitedBy'])
            ->get();
    }

    /**
     * Add a participant to a ticket.
     *
     * @param string $ticketId
     * @param string $userId
     * @param string $invitedByUserId
     * @param string $role
     * @return TicketParticipant
     * @throws \Exception
     */
    public function addParticipant(
        string $ticketId,
        string $userId,
        string $invitedByUserId,
        string $role
    ): TicketParticipant {
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
            return $existingParticipant->load(['user', 'invitedBy']);
        }

        return TicketParticipant::create([
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'invited_by_user_id' => $invitedByUserId,
            'role_in_ticket' => $role,
            'joined_at' => now(),
        ])->load(['user', 'invitedBy']);
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

        return $participant->update([
            'left_at' => now(),
        ]);
    }
}
