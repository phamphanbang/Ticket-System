<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Services\CommentService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(protected CommentService $commentService)
    {

    }

    public function store(CreateCommentRequest $request, $ticket_id)
    {
        $validated = $request->validated();

        $user = auth()->user();

        $validated['user_id'] = $user->id;
        $validated['ticket_id'] = $ticket_id;
        $validated['user_type'] = get_class($user);

        $data = $this->commentService->staffCommentTicket($validated);

        return response()->success(
            new CommentResource($data),
            __('messages.model_created', ['model' => 'Comment'])
        );
    }
}
