<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
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
