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
        'priority',
        'deadline',
        'status',
        'client_id'
    ];

    protected $casts = [
        'status' => TicketStatus::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deadline' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

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
