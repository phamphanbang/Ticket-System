<?php

namespace App\Http\Controllers;

use App\Constants\TicketStatus;
use App\Http\Requests\AdminAssignsTicketRequest;
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

    public function index(Request $request)
    {
        $data = $this->ticketService->getListTicket($request);
        return response()->success(
            $data,
            __('messages.model_list', ['model' => 'Ticket'])
        );
    }

    public function adminCreateTicket(AdminCreateTicketRequest $request)
    {

        $validated = $request->validated();
        $validated['assign_to'] = $validated['assign_to'] ?? null;
        $validated['status'] = TicketStatus::New;

        $data = $this->ticketService->adminCreateTicket($validated);

        return response()->success(
            new TicketResource($data),
            __('messages.model_created', ['model' => 'Ticket'])
        );
    }

    public function adminAssignTicket(AdminAssignsTicketRequest $request,$id)
    {
        $validated = $request->validated();

        $data = $this->ticketService->adminAssignTicket($validated,$id);
        return response()->success(
            new TicketResource($data),
            __('messages.ticket_assigned')
        );
    }

    public function staffConfirmTicket(Request $request, $id)
    {
        // $validated = $request->validated();
        $validated['status'] = TicketStatus::InProgress;

        $data = $this->ticketService->staffConfirmTicket($validated, $id);

        return response()->success(
            new TicketResource($data),
            __('messages.ticket_processed')
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
