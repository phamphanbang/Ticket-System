<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TicketLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'action',
        'message_id',
        'client_name',
        'client_email',
        'from_status',
        'to_status',
        'description',
    ];
    
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
