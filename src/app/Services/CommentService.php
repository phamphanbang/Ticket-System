<?php

namespace App\Services;

use App\Constants\PaginateConstant;
use App\Constants\UserRoles;
use App\Mail\ClientStaffCreateComment;
use App\Models\Comment;
use App\Validators\TicketValidator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CommentService
{
  public function __construct(
    protected TicketMailService $ticketMailService,
    protected TicketService $ticketService
  ) {
    // Constructor to inject TicketMailService dependency
  }

  public function getListComment($request,$ticket_id)
  {
    $isPaginate = $request->boolean('isPaginate', true);

    $query = Comment::query()->where('ticket_id', $ticket_id)->with('user');

    if ((boolean) $isPaginate) {
      $perPage = $request->input('perPage', PaginateConstant::DEFAULT_PER_PAGE->value);
      $page = $request->input('page', PaginateConstant::DEFAULT_PAGE->value);
      $offset = ($page - 1) * $perPage;
      if ($offset < 0) {
        $offset = PaginateConstant::DEFAULT_OFFSET->value;
      }
      $query = $query->offset($offset)->limit($perPage);
      $total = $query->count();
    }

    $comments = $query->get()->map(function ($comment) {
      return [
        'id' => $comment->id,
        'body' => $comment->body,
        'ticket_id' => $comment->ticket_id,
        'created_at' => $comment->created_at,
        'updated_at' => $comment->updated_at,
        'user' => [
          'id' => $comment->user->id,
          'name' => $comment->user->name,
          'email' => $comment->user->email
        ]
      ];
    });

    return $isPaginate ? [
      'data' => $comments,
      'pagination' => [
        'total' => $total,
        'page' => (int) $page,
        'perPage' => (int) $perPage
      ]
    ] : [
      'data' => $comments,
    ];
  }

  public function createComment($data)
  {
    $comment = Comment::create($data);

    return $comment;
  }

  public function staffCommentTicket($comment)
  {
    $ticket = $this->ticketService->getTicketById($comment['ticket_id']);
    if(auth()->user()->role != UserRoles::ADMIN->value) {
      TicketValidator::checkStaffIsAssignedToTicket($ticket,$comment['user_id']);
    }

    $client = $ticket->client;
    $comment = $this->createComment($comment);

    Mail::to($client->email)->queue(new ClientStaffCreateComment($comment, $ticket, $client));

    return $comment;
    
  }
}
