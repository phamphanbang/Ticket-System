<?php

namespace App\Models;

use App\Constants\TicketStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasUuids;
    protected $fillable = [
        'title',
        'description',
        'assign_to',
        'client_name',
        'client_email',
        'priority',
        'deadline',
        'status',
        'message_id',
    ];

    protected $casts = [
        'status' => TicketStatus::class,
    ];

    public function assignTo()
    {
        return $this->belongsTo(User::class, 'assign_to');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function logs()
    {
        return $this->hasMany(TicketLog::class);
    }

    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
