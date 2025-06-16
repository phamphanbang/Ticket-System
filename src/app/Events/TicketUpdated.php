<?php

namespace App\Events;

use App\Http\Resources\TicketResource;
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

class TicketUpdated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  /**
   * Create a new event instance.
   */
  public function __construct(public Ticket $ticket)
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
      new Channel('tickets.' . $this->ticket->id),
    ];
  }

  public function broadcastAs(): string
  {
    return 'ticket.updated';
  }

  public function broadcastWith()
  {
    return [
      'id' => $this->ticket->id,
      'title' => $this->ticket->title,
      'description' => $this->ticket->description,
      'client_email' => $this->ticket->client?->email,
      'client_name' => $this->ticket->client?->name,
      'status' => $this->ticket->status,
      'holder' => $this->ticket->holder,
      'staff' => $this->ticket->staff ?? [],
      'created_at' => $this->ticket->created_at->format('Y-m-d H:i:s'),
      'updated_at' => $this->ticket->updated_at->format('Y-m-d H:i:s'),
    ];
  }
}
