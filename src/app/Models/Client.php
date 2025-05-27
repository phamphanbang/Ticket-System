<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasUuids;
    protected $fillable = [
        'name',
        'email'
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function comments()
    {
        $this->morphMany(Comment::class,'user');
    }
}
