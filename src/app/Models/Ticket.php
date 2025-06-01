<?php

namespace App\Models;

use App\Constants\InternalStatus;
use App\Constants\ExternalStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasUuids;
    
    protected $fillable = [
        'subject',
        'description',
        'internal_status',
        'external_status',
        'client_id'
    ];

    protected $casts = [
        'internal_status' => InternalStatus::class,
        'external_status' => ExternalStatus::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class);
    }

    public function logs()
    {
        return $this->hasMany(TicketAuditLog::class);
    }

    public function receivedEmails()
    {
        return $this->hasMany(ReceivedEmail::class);
    }

    public function scopeWithInternalStatus($query, $status)
    {
        return $query->where('internal_status', $status);
    }

    public function scopeWithExternalStatus($query, $status) 
    {
        return $query->where('external_status', $status);
    }
}
