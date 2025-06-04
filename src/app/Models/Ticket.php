<?php

namespace App\Models;

use App\Constants\InternalStatus;
use App\Constants\ExternalStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'title',
        'description',
        'status',
        'client_id',
        'mail_created_id',
        'created_by',
        'current_processor_id',
        'responsible_user_id',
        'closed_at'
    ];

    protected $casts = [
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function currentProcessor()
    {
        return $this->belongsTo(User::class, 'current_processor_id');
    }

    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function mailCreated()
    {
        return $this->belongsTo(ReceivedEmail::class, 'mail_created_id');
    }

    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    


}
