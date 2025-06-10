<?php

namespace App\Events;

use App\Http\Resources\AuditLogResource;
use App\Http\Resources\UserResource;
use App\Models\TicketAuditLog;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class AuditLogged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public function __construct(public TicketAuditLog $auditLog) {}

  public function broadcastOn(): array
  {
    return [
      new PrivateChannel('tickets.' . $this->auditLog->ticket_id . '.logs'),
    ];
  }

  public function broadcastAs(): string
  {
    return 'audit.logged';
  }

  public function broadcastWith(): array
  {
    return [
      'id' => $this->auditLog->id,
      'ticket_id' => $this->auditLog->ticket_id,
      'action' => $this->auditLog->action,
      'status' => $this->auditLog->status,
      'to_status' => $this->auditLog->to_status,
      'holder' => $this->auditLog->holder ?? [],
      'staff' => $this->auditLog->staff ?? [],
      'start_at' => $this->auditLog->start_at->format('Y-m-d H:i:s'),
      'end_at' => $this->auditLog->end_at?->format('Y-m-d H:i:s'),
    ];
  }
}
