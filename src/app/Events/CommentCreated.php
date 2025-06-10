<?php

namespace App\Events;

use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentCreated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  /**
   * Create a new event instance.
   */
  public function __construct(public TicketComment $comment)
  {
    //
  }

  /**
   * Get the channels the event should broadcast on.
   *
   * @return array<int, \Illuminate\Broadcasting\Channel>
   */
  public function broadcastOn(): array
  {
    return [
      new PrivateChannel('tickets.' . $this->comment->ticket_id . '.comments'),
    ];
  }

  public function broadcastWith()
  {
    return [
      'id' => $this->comment->id,
      'content' => $this->comment->content,
      'created_at' => $this->comment->created_at,
      'updated_at' => $this->comment->updated_at,
      'user' => $this->comment->user ? [
        'id' => $this->comment->user->id,
        'name' => $this->comment->user->name,
      ] : [],
      'attachments' => $this->comment->attachments ?? [],
    ];
  }
}
