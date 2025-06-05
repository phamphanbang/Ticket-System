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
        'comment_id',
        'file_path',
        'file_name',
        'file_size',
        'file_extension',
        'content_type',
    ];

    public function comment()
    {
        return $this->belongsTo(TicketComment::class , 'comment_id');
    }
    
    
}
