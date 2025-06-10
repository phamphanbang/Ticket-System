<?php

namespace App\Events;

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
      new PrivateChannel('tickets.' . $this->ticket->id),
    ];
  }

  public function broadcastWith()
  {
    return [
      'id' => $this->ticket->id,
      'title' => $this->ticket->title,
      'description' => $this->ticket->description,
      'status' => $this->ticket->status,
      'holder' => $this->ticket->holder ? [
        'id' => $this->ticket->holder->id,
        'name' => $this->ticket->holder->name,
      ] : [],
      'staff' => $this->ticket->staff ? [
        'id' => $this->ticket->staff->id,
        'name' => $this->ticket->staff->name,
      ] : [],
      'created_at' => $this->ticket->created_at,
      'updated_at' => $this->ticket->updated_at,
    ];
  }
}
