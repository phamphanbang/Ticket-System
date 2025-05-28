<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
  public function toArray(Request $request)
  {
    return [
      'id' => $this->id,
      'body' => $this->body,
      'user_id' => $this->user_id,
      'user_type' => $this->user_type,
      'ticket_id' => $this->ticket_id,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
      'user' => [
        'id' => $this->user?->id,
        'name' => $this->user?->name,
        'email' => $this->user?->email,
        'role' => $this->user?->role,
      ],
      'mail' => $this->mail?->toArray() ?? null,
    ];
  }
}