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
        'user_type',
        'body',
    ];
    
    public function user()
    {
        return $this->morphTo(__FUNCTION__,'user_type','user_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

}
