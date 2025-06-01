<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketParticipantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_id' => $this->ticket_id,
            'role_in_ticket' => $this->role_in_ticket,
            'joined_at' => $this->joined_at->format('Y-m-d H:i:s'),
            'left_at' => $this->left_at?->format('Y-m-d H:i:s'),
            'user' => new UserResource($this->whenLoaded('user')),
            'invited_by' => new UserResource($this->whenLoaded('invitedBy')),
        ];
    }
}
