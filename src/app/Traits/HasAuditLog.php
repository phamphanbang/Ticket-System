<?php

namespace App\Traits;

use App\Models\TaskAuditLog;
use App\Models\TicketAuditLog;
use Illuminate\Support\Facades\Auth;

trait HasAuditLog
{
    protected function createAuditLog(
        string $modelId,
        string $fieldChanged,
        $oldValue,
        $newValue,
        string $reason,
        string $changeType = 'update'
    ): void {
        $modelClass = str_replace('Service', '', class_basename($this));
        $auditLogClass = "App\\Models\\{$modelClass}AuditLog";

        $auditLogClass::create([
            strtolower($modelClass) . '_id' => $modelId,
            'changed_by' => Auth::id() ,
            'field_changed' => $fieldChanged,
            'old_value' => is_array($oldValue) ? json_encode($oldValue) : $oldValue,
            'new_value' => is_array($newValue) ? json_encode($newValue) : $newValue,
            'change_type' => $changeType,
            'reason' => $reason,
            'created_at' => now()
        ]);
    }

    protected function createTaskAuditLog(
        string $taskId,
        string $fieldChanged,
        $oldValue,
        $newValue,
        string $reason,
        string $changeType = 'update',
        string $currentStatus,
        string $currentPhase
    ): void {
        TaskAuditLog::create([
            'task_id' => $taskId,
            'changed_by' => Auth::id(),
            'field_changed' => $fieldChanged,
            'old_value' => is_array($oldValue) ? json_encode($oldValue) : $oldValue,
            'new_value' => is_array($newValue) ? json_encode($newValue) : $newValue,
            'current_status' => $currentStatus,
            'current_phase' => $currentPhase,
            'change_type' => $changeType,
            'reason' => $reason,
            'created_at' => now()
        ]);
    }

    protected function createTicketAuditLog(
        string $ticketId,
        string $fieldChanged,
        $oldValue,
        $newValue,
        string $reason,
        string $changeType = 'update'
    ): void {
        TicketAuditLog::create([
            'ticket_id' => $ticketId,
            'changed_by' => Auth::id(),
            'field_changed' => $fieldChanged,
            'old_value' => is_array($oldValue) ? json_encode($oldValue) : $oldValue,
            'new_value' => is_array($newValue) ? json_encode($newValue) : $newValue,
            'change_type' => $changeType,
            'reason' => $reason,
            'created_at' => now()
        ]);
    }
} 