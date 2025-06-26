<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UserSlackConnection extends Model
{
  use HasFactory, HasUuids;

  protected $fillable = [
    'user_id',
    'slack_user_id',
    'slack_team_id',
    'slack_channel_id',
    'access_token',
    'connected_at',
    'disconnected_at',
  ];

  protected $hidden = [
    'slack_access_token',
  ];

  protected $casts = [
    'connected_at' => 'datetime',
    'disconnected_at' => 'datetime',
  ];

  public $incrementing = false;
  protected $keyType = 'string';

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function isConnected()
  {
    return $this->disconnected_at === null;
  }
}
