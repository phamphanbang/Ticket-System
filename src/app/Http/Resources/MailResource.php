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
    $user = null;
    if ($this->type === 'received') {
      $user = Client::where('email', $this->from_email)->first();
    } else {
      $user = User::where('email', $this->from_email)->first();
    }
    // dd($this);
    return [
      "id"=> $this->id,
      "from"=> $user->name,
      "subject"=> $this->subject,
      "body"=> $this->body,
      "attachments"=> $this->attachments,
      "created_at"=> $this->created_at->format('Y-m-d H:i:s'),
      "updated_at"=> $this->updated_at->format('Y-m-d H:i:s'),
    ];
  }
}
