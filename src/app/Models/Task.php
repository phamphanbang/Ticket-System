<?php

namespace App\Models;

use App\Constants\EstimationStatus;
use App\Constants\ExecutionStatus;
use App\Constants\TaskPhase;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasUuids;

    protected $fillable = [
        'ticket_id',
        'title', 
        'description',
        'assigned_to',
        'estimation_status',
        'execution_status',
        'phase',
        'estimated_time',
        'actual_time'
    ];

    protected $casts = [
        'estimation_status' => EstimationStatus::class,
        'execution_status' => ExecutionStatus::class,
        'phase' => TaskPhase::class,
        'estimated_time' => 'integer',
        'actual_time' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeInPhase($query, $phase)
    {
        return $query->where('phase', $phase);
    }

    public function scopeWithEstimationStatus($query, $status)
    {
        return $query->where('estimation_status', $status);
    }

    public function scopeWithExecutionStatus($query, $status)
    {
        return $query->where('execution_status', $status);
    }
}
