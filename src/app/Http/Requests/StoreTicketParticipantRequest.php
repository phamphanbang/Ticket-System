<?php

namespace App\Http\Requests;

use App\Models\TicketParticipant;
use Illuminate\Foundation\Http\FormRequest;

class StoreTicketParticipantRequest extends FormRequest
{
  public function authorize(): bool
  {
    return auth()->check() && auth()->user()->hasAnyRole(['supporter', 'leader','admin']);
  }

  public function rules(): array
  {
    return [
      'user_id' => 'required',
      'user_id.*' => 'required|string|exists:users,id'
    ];
  }
}
