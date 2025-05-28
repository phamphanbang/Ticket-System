<?php

namespace App\Services;

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

  public function createComment($data)
  {
    $comment = Comment::create($data);

    return $comment;
  }

  public function staffCommentTicket($comment)
  {
    $ticket = $this->ticketService->getTicketById($comment['ticket_id']);
    TicketValidator::checkStaffIsAssignedToTicket($ticket,$comment['user_id']);

    $client = $ticket->client;
    $comment = $this->createComment($comment);

    Mail::to($client->email)->queue(new ClientStaffCreateComment($comment, $ticket, $client));

    return $comment;
    
  }
}
