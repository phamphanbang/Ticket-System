<?php

namespace App\Traits;

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
} 