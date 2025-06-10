<?php

namespace App\Events;

use App\Http\Resources\CommentResource;
use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentUpdated implements ShouldBroadcast, ShouldDispatchAfterCommit
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
      new Channel('tickets.' . $this->comment->ticket_id . '.comments'),
    ];
  }

  public function broadcastAs(): string
  {
    return 'comment.updated';
  }

  public function broadcastWith()
  {
    return [
      'id' => $this->comment->id,
      'content' => $this->comment->content,
      'ticket' => $this->comment->ticket,
      'user' => $this->comment->user,
      'attachments' => $this->comment->attachments ?? [],
      'created_at' => $this->comment->created_at->format('Y-m-d H:i:s'),
      'updated_at' => $this->comment->updated_at->format('Y-m-d H:i:s'),
    ];
  }
}
