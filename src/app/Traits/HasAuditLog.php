<?php

namespace App\Traits;

use App\Models\TaskAuditLog;
use App\Models\TicketAuditLog;
use Illuminate\Support\Facades\Auth;

trait HasAuditLog
{
    protected function createTicketAuditLog(
        string $ticketId,
        string $action,
        ?string $fromStatus = null,
        ?string $toStatus = null,
        ?string $fromUserId = null,
        ?string $toUserId = null,
        ?string $startAt = null,
        ?string $endAt = null,
        ?string $comment = null
    ): void {
        TicketAuditLog::create([
            'ticket_id' => $ticketId,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'from_user_id' => $fromUserId,
            'to_user_id' => $toUserId,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'comment' => $comment,
            'created_at' => now()
        ]);
    }
} 