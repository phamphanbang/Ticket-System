<?php

namespace App\Http\Controllers;

use App\Constants\TicketStatus;
use App\Http\Requests\ActionTicketRequest;
use App\Http\Requests\AdminAssignsTicketRequest;
use App\Http\Requests\CreateTicketRequest;
use App\Http\Requests\DelayTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Services\TaskService;
use App\Services\TicketService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class TicketController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected TicketService $ticketService,
        protected TaskService $taskService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only([
            'search',
            'internal_status',
            'external_status',
            'sort_by',
            'sort_direction',
            'limit',
            'page'
        ]);

        $result = $this->ticketService->index($filters);

        return $this->success([
            'data' => TicketResource::collection($result['data']),
            'pagination' => $result['pagination']
        ], 'Tickets retrieved successfully');
    }

    public function store(CreateTicketRequest $request)
    {
        $validated = $request->validated();

        $data = $this->ticketService->store($validated);

        return $this->success(
            new TicketResource($data),
            __('messages.model_created', ['model' => 'Ticket'])
        );
    }

    public function show(string $id)
    {
        $ticket = $this->ticketService->getTicketById($id);

        return $this->success(new TicketResource($ticket), 'Ticket retrieved successfully');
    }

    public function update(UpdateTicketRequest $request, string $id)
    {
        $validated = $request->validated();

        $ticket = $this->ticketService->update($id, $validated);

        return $this->success(
            new TicketResource($ticket),
            __('messages.model_updated', ['model' => 'Ticket'])
        );
    }

    public function executeTicket(string $id)
    {
        $ticket = $this->ticketService->changeToExecutionTicket($id);
        $this->taskService->changeTasksToExecution($id);
        return $this->success(
            new TicketResource($ticket),
            'Ticket status changed to In Progress successfully'
        );
    }

    public function checkAndCloseTicket(string $id)
    {
        $ticket = $this->ticketService->checkAndCloseTicket($id);

        return $this->success(
            new TicketResource($ticket),
            'Ticket status checked successfully'
        );
    }

    public function getTicketAuditLogs(string $id)
    {
        $logs = $this->ticketService->getTicketAuditLogs($id);

        return $this->success(
            $logs,
            'Ticket audit logs retrieved successfully'
        );
    }
}
