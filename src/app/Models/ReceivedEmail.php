<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceivedEmail extends Model
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
        'ticket_id',
        'from_email',
        'to_email',
        'subject',
        'body',
        'attachments',
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
        'attachments' => 'json',
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

    /**
     * The possible email statuses.
     *
     * @var array<string>
     */
    public const STATUSES = [
        'pending',
        'processed',
        'ignored',
        'error',
    ];

    /**
     * Get the ticket associated with this email.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Check if the email has attachments.
     */
    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    /**
     * Check if the email is a reply to another email.
     */
    public function isReply(): bool
    {
        return !is_null($this->in_reply_to);
    }
} 