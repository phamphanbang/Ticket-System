<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketEmail extends Model
{
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'message_id',
        'in_reply_to',
        'references',
        'ticket_id',
        'from_email',
        'from_name',
        'to_email',
        'subject',
        'body',
        'type',
        'status',
        'received_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'received_at' => 'datetime',
    ];

    /**
     * The possible email types.
     *
     * @var array<string>
     */
    public const TYPES = [
        'new_ticket',
        'reply',
        'feedback',
        'unknown',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class,'ticket_id');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class,'email_id');
    }
} 