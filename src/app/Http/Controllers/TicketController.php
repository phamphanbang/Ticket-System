<?php

namespace App\Http\Controllers;

use App\Constants\TicketStatus;
use App\Http\Requests\AdminCreateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Services\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    private $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    public function adminCreateTicket(AdminCreateTicketRequest $request)
    {

        $validated = $request->validated();
        $validated['assign_to'] = $validated['assign_to'] ?? null;
        $validated['status'] = $validated['assign_to'] ? TicketStatus::IN_PROGRESS : TicketStatus::NEW;
        // if (array_key_exists('assigned_to', $validated)) {
        //     $validated['assigned_to'] = $validated['assigned_to'] ?? null;
        //     $validated['status'] = TicketStatus::IN_PROGRESS;
        // }

        $ticket = $this->ticketService->createTicket($validated);

        return response()->success(
            new TicketResource($ticket),
            __('messages.model_created', ['model' => 'Ticket'])
        );
    }

    public function show($id)
    {
        $ticket = $this->ticketService->getTicketById($id);

        return response()->success(
            $ticket,
            __('messages.model_get_success', ['model' => 'Ticket'])
        );
    }
}
