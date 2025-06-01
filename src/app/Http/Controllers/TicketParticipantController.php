<?php

namespace App\Http\Controllers;

use App\Services\TicketParticipantService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreTicketParticipantRequest;

class TicketParticipantController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly TicketParticipantService $ticketParticipantService
    ) {}

    /**
     * Get all participants for a specific ticket.
     *
     * @param string $ticketId
     * @return JsonResponse
     */
    public function index(string $ticketId): JsonResponse
    {
        $participants = $this->ticketParticipantService->getTicketParticipants($ticketId);

        return $this->success($participants, 'Participants retrieved successfully');
    }

    /**
     * Add a participant to a ticket.
     *
     * @param StoreTicketParticipantRequest $request
     * @param string $ticketId
     * @return JsonResponse
     */
    public function store(StoreTicketParticipantRequest $request, string $ticketId): JsonResponse
    {
        $validated = $request->validated();
        $participant = $this->ticketParticipantService->addParticipant(
            ticketId: $ticketId,
            userId: $validated['user_id'],
            invitedByUserId: $request->user()->id,
            role: $validated['role']
        );

        return $this->success($participant, 'Participant added successfully', 201);
    }

    /**
     * Remove a participant from a ticket.
     *
     * @param string $participantId
     * @return JsonResponse
     */
    public function destroy(string $participantId): JsonResponse
    {
        $this->ticketParticipantService->removeParticipant($participantId, request()->user()->id);

        return $this->success(null, 'Participant removed successfully');
    }
} 