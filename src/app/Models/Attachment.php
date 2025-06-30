<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'ticket_id',
        'comment_id',
        'email_id',
        'file_path',
        'file_name',
        'file_size',
        'file_extension',
        'content_type',
        'file_status'
    ];

    public function comment()
    {
        return $this->belongsTo(TicketComment::class , 'comment_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class ,'ticket_id');
    }
    
    public function email()
    {
        return $this->belongsTo(TicketEmail::class ,'email_id');
    }
    
}
