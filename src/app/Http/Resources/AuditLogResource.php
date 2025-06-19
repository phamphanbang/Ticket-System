<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
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
            'action' => $this->action,
            'status' => $this->status,
            'to_status' => $this->to_status,
            'holder' => $this->when($this->holder_id, new UserResource($this->holder)),
            'staff' => $this->when($this->staff_id, new UserResource($this->staff)),
            'start_at' => $this->start_at->format('Y-m-d H:i:s'),
            'end_at' => $this->end_at?->format('Y-m-d H:i:s'),
        ];
    }
}
