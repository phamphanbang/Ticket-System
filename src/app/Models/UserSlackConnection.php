<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSlackConnection extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'slack_user_id',
    'slack_team_id',
    'slack_access_token',
  ];

  protected $hidden = [
    'slack_access_token',
  ];

  protected $casts = [
    'connected_at' => 'datetime',
    'disconnected_at' => 'datetime',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
