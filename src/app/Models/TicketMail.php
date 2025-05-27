<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TicketMail extends Model
{
    use HasUuids;

    protected $fillable = [
        'ticket_id',
        'message_id',
        'from_email',
        'from_name',
        'in_reply_to',
        'subject',
        'raw_email',
        'parse_email',
        'references'
    ];

    protected $casts = [
        'references' => 'array',
    ];

    public function comment()
    {
        return $this->hasOne(Comment::class,'mail_id','id');
    }
}
