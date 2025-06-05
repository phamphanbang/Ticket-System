<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Services\CommentService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    use ApiResponse;
    public function __construct(protected CommentService $commentService) {}

    public function index(Request $request, $ticket_id)
    {
        $data = $this->commentService->index($ticket_id, $request->all());
        return $this->success(
            $data,
            __('messages.model_list', ['model' => 'Comment'])
        );
    }

    public function store(CreateCommentRequest $request, $ticket_id)
    {
        $validated = $request->validated();

        $user = auth()->user();

        $validated['user_id'] = $user->id;
        $validated['ticket_id'] = $ticket_id;

        $data = $this->commentService->store($ticket_id, $validated);

        return $this->success(
            $data,
            __('messages.model_created', ['model' => 'Comment'])
        );
    }

    public function show(Request $request, $ticket_id, $id)
    {
        $data = $this->commentService->show($ticket_id, $id);
        return $this->success(
            $data,
            __('messages.model_show', ['model' => 'Comment'])
        );
    }

    public function update(UpdateCommentRequest $request, $id)
    {
        $validated = $request->validated();

        $data = $this->commentService->update($id, $validated);
        return $this->success(
            $data,
            __('messages.model_updated', ['model' => 'Comment'])
        );
    }

    public function destroy(Request $request, $id)
    {
        $this->commentService->destroy($id);
        return $this->success(
            __('messages.model_deleted', ['model' => 'Comment'])
        );
    }
}
