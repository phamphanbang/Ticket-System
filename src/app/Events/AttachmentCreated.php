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
use Illuminate\Support\Collection;

class AttachmentCreated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  /**
   * Create a new event instance.
   */
  public function __construct(public Collection $attachments, public Ticket $ticket)
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
      new Channel('tickets.' . $this->ticket->id . '.attachments'),
    ];
  }

  public function broadcastAs(): string
  {
    return 'ticket.updated';
  }

  public function broadcastWith()
  {
    $attachments = [];
    foreach ($this->attachments as $attachment) {
      $attachments[] = [
        'id' => $attachment['id'],
        'file_path' => $attachment['file_path'],
        'file_name' => $attachment['file_name'],
        'file_size' => $attachment['file_size'],
        'file_extension' => $attachment['file_extension'],
        'created_at' => $attachment['created_at']->format('Y-m-d H:i:s'),
      ];
    }
    return $attachments;
  }
}
