<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketParticipant extends Model
{
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'ticket_id',
        'user_id',
        'invited_by_user_id',
        'role_in_ticket',
        'joined_at',
        'left_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    /**
     * The possible roles in a ticket.
     *
     * @var array<string>
     */
    public const ROLES = [
        'client',
        'staff',
        'leader',
        'supporter',
        'admin',
    ];

    /**
     * Get the ticket that the participant belongs to.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the user who is participating.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who invited this participant.
     */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    /**
     * Check if the participant is active (has not left).
     */
    public function isActive(): bool
    {
        return is_null($this->left_at);
    }
} 