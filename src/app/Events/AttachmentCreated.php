<?php

namespace App\Events;

use App\Http\Resources\TicketResource;
use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\TicketAuditLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttachmentCreated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  /**
   * Create a new event instance.
   */
  public function __construct(public Attachment $attachment)
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
      new Channel('tickets.' . $this->attachment->ticket_id . '.attachments'),
    ];
  }

  public function broadcastAs(): string
  {
    return 'ticket.updated';
  }

  public function broadcastWith()
  {
    return [
      'id' => $this->attachment->id,
      'ticket_id' => $this->attachment->ticket_id,
      'comment_id' => $this->attachment->comment_id,
      'file_path' => $this->attachment->file_path,
      'file_name' => $this->attachment->file_name,
      'file_size' => $this->attachment->file_size,
      'file_extension' => $this->attachment->file_extension,
      'content_type' => $this->attachment->content_type,
      'created_at' => $this->attachment->created_at->format('Y-m-d H:i:s'),
      'updated_at' => $this->attachment->updated_at->format('Y-m-d H:i:s'),
    ];
  }
}
