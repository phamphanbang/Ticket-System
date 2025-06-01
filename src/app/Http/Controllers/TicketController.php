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
use App\Services\TicketService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class TicketController extends Controller
{
    use ApiResponse;

    private $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    public function index(Request $request)
    {
        $filters = $request->only([
            'search',
            'internal_status',
            'external_status',
            'created_by',
            'sort_by',
            'sort_direction',
            'per_page',
            'page'
        ]);

        $result = $this->ticketService->index($filters);

        return $this->success([
            'items' => TicketResource::collection($result['items']),
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

   
}
