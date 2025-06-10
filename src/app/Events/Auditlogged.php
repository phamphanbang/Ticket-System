<?php

namespace App\Events;

use App\Models\TicketAuditLog;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class Auditlogged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public function __construct(public TicketAuditLog $auditLog) {}

  public function broadcastOn(): array
  {
    return [
      new PrivateChannel('tickets.' . $this->auditLog->ticket_id . '.logs'),
    ];
  }

  public function broadcastWith(): array
  {
    return [
      'id' => $this->auditLog->id,
      'action' => $this->auditLog->action,
      'status' => $this->auditLog->status,
      'to_status' => $this->auditLog->to_status,
      'holder' => $this->auditLog->holder ? [
        'id' => $this->auditLog->holder->id,
        'name' => $this->auditLog->holder->name,
      ] : [],
      'staff' => $this->auditLog->staff ? [
        'id' => $this->auditLog->staff->id,
        'name' => $this->auditLog->staff->name,
      ] : [],
      'start_at' => $this->auditLog->start_at,
      'end_at' => $this->auditLog->end_at,
      'created_at' => $this->auditLog->created_at,
      'updated_at' => $this->auditLog->updated_at,
    ];
  }
}
