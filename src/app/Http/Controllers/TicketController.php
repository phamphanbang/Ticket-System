<?php

namespace App\Http\Controllers;

use App\Constants\TicketStatus;
use App\Http\Requests\ActionTicketRequest;
use App\Http\Requests\AdminAssignsTicketRequest;
use App\Http\Requests\AdminCreateTicketRequest;
use App\Http\Requests\DelayTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
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

    public function update(UpdateTicketRequest $request, $id)
    {
        $validated = $request->validated();

        $data = $this->ticketService->update($validated, $id);
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
            __('messages.ticket_confirmed')
        );
    }

    public function staffResolveTicket(Request $request, $id)
    {
        $validated['status'] = TicketStatus::Resolved;

        $data = $this->ticketService->staffResolveTicket($validated, $id);

        return response()->success(
            new TicketResource($data),
            __('messages.ticket_resolved')
        );
    }

    public function action(ActionTicketRequest $request, $id) 
    {
        $validated = $request->validated();

        $newStatus = (int) $validated['status'];

        switch ($newStatus) {
            case TicketStatus::InProgress->value:
                $data = $this->ticketService->staffConfirmTicket($validated, $id);
                $message = __('messages.ticket_confirmed');
                break;
            case TicketStatus::Resolved->value:
                $data = $this->ticketService->staffResolveTicket($validated, $id);
                $message = __('messages.ticket_resolved');
                break;
            default:
                $data = null;
                $message = __('messages.invalid_ticket_status');
                return response()->error(
                    $message,
                    [],
                    400
                );
        }

        return response()->success(
            new TicketResource($data),
            $message
        );
    }

    public function staffDelayTicket(DelayTicketRequest $request, $id)
    {
        $validated = $request->validated();

        $data = $this->ticketService->staffDelayTicket($validated, $id);
        return response()->success(
            new TicketResource($data),
            __('messages.ticket_delayed')
        );
    }

    public function clientRejectTicket(Request $request,$id)
    {
        $data = $this->ticketService->clientRejectTicket($request->all(), $id);
        return response()->success(
            new TicketResource($data),
            __('messages.ticket_rejected')
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
