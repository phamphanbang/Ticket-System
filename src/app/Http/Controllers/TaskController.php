<?php

namespace App\Http\Controllers;

use App\Services\TaskService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected TaskService $taskService
    ) {}

    public function index(Request $request, $ticket_id): JsonResponse
    {
        $tasks = $this->taskService->index($request->all(), $ticket_id);
        return $this->success($tasks);
    }

    public function store(Request $request, $ticket_id): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'assigned_to' => 'nullable|uuid|exists:users,id',
        ]);
        $validated['ticket_id'] = $ticket_id;
        $task = $this->taskService->store($validated);
        return $this->success($task, 'Task created successfully', 201);
    }

    public function show(string $id): JsonResponse
    {
        $task = $this->taskService->show($id);
        return $this->success($task);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'estimated_time' => 'nullable|integer|min:0',
        ]);

        $task = $this->taskService->update($id, $validated);
        return $this->success($task, 'Task updated successfully');
    }

    public function assignStaff(Request $request, string $id)
    {
        $validated = $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
        ]);
        $staff_id = $validated['user_id'];
        $task = $this->taskService->assignStaff($id, $staff_id);
        return $this->success($task, 'Task assigned to staff successfully');
    }

    public function readyToReview(Request $request, string $id)
    {
        $task = $this->taskService->readyToReview($id);
        $this->taskService->notifyLeaderForReview($id);
        return $this->success($task, 'Task ready to review');
    }

    public function needsRevision(Request $request, string $id)
    {
        $revision_reason = $request->input('revision_reason');
        $task = $this->taskService->markEstimateNeedsRevision($id, $revision_reason);
        return $this->success($task, 'Task estimate needs revision');
    }

    public function estimateApproved(Request $request, string $id)
    {
        $task = $this->taskService->markEstimateApproved($id);
        return $this->success($task, 'Task estimate approved');
    }

    public function startExecution(string $id): JsonResponse
    {
        $task = $this->taskService->startExecution($id);
        return $this->success($task, 'Task execution started successfully');
    }

    public function destroy(string $id): JsonResponse
    {
        $this->taskService->destroy($id);
        return $this->success(null, 'Task deleted successfully', 204);
    }
}
