<?php

namespace App\Http\Controllers;

use App\Services\TicketParticipantService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreTicketParticipantRequest;
use App\Services\TicketService;
use Illuminate\Support\Facades\Request;

class TicketParticipantController extends Controller
{
  use ApiResponse;

  public function __construct(
    private readonly TicketParticipantService $ticketParticipantService,
    private readonly TicketService $ticketService
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
    $ticket = $this->ticketService->checkAndUpdateInitialStatus($ticketId);
    $participant = $this->ticketParticipantService->addParticipant(
      ticketId: $ticketId,
      userIds: $validated['user_id'],
      invitedByUserId: $request->user()->id
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

  public function destroyMultiple(Request $request,string $ticketId): JsonResponse
  {
    $validated = $request->validate([
      'participant_ids' => 'required|array',
      'participant_ids.*' => 'required|string|exists:ticket_participants,id'
    ]);

    $removedParticipants = $this->ticketParticipantService->removeParticipants(
      $validated['participant_ids'],
      $request->user()->id,
      $ticketId
    );

    return $this->success([
      'removed_participants' => $removedParticipants
    ], 'Participants removed successfully');
  }
}
