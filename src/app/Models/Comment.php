<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasUuids;
    protected $fillable = [
        'ticket_id',
        'user_id',
        'body',
        'message_id',
        'in_reply_to',
        'references',
        'client_name',
        'client_email',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }


}
