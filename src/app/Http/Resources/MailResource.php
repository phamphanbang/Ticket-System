<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MailResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    // dd($this);
    return [
      "id"=> $this->id,
      "from_name"=> $this->from_name,
      "from_email"=> $this->from_email,
      "subject"=> $this->subject,
      "body"=> $this->body,
      "attachments"=> $this->attachments,
      "created_at"=> $this->created_at->format('Y-m-d H:i:s'),
      "updated_at"=> $this->updated_at->format('Y-m-d H:i:s'),
    ];
  }
}
