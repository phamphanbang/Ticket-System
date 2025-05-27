<?php

namespace App\Services;

use App\Models\Comment;

class CommentService
{
  public function __construct(
    protected TicketMailService $ticketMailService
  ) {
    // Constructor to inject TicketMailService dependency
  }

  public function createComment($data)
  {
    $comment = Comment::create($data);

    return $comment;
  }
}
