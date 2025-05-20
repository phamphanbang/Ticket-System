<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    
    protected $fillable = [
        'title',
        'description',
        'status',
        'assigned_to',
        'client_name',
        'client_email',
        'priority',
        'deadline',
        'message_id',    
    ];

    public function assignTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function logs()
    {
        return $this->hasMany(TicketLog::class);
    }
}
