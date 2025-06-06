<?php

namespace App\Http\Controllers;


use App\Http\Requests\CreateTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Services\TaskService;
use App\Services\TicketService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class TicketController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected TicketService $ticketService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only([
            'search',
            'status',
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
        $ticket = $this->ticketService->show($id);

        return $this->success(
            new TicketResource($ticket),
            'Ticket retrieved successfully'
        );
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

    public function destroy(string $id)
    {
        $this->ticketService->delete($id);

        return $this->success(
            null,
            __('messages.model_deleted', ['model' => 'Ticket'])
        );
    }
    public function getLogs(string $id)
    {
        $logs = $this->ticketService->getLogs($id);

        return $this->success(
            $logs,
            'Ticket audit logs retrieved successfully'
        );
    }

    public function deleteLog(string $id)
    {
        $this->ticketService->deleteLog($id);

        return $this->success(
            null,
            __('messages.model_deleted', ['model' => 'Ticket audit log'])
        );
    }
}
