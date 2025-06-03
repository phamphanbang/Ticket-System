<?php

namespace App\Http\Requests;

use App\Constants\UserRoles;
use App\Models\TicketParticipant;
use Illuminate\Foundation\Http\FormRequest;

class StoreTicketParticipantRequest extends FormRequest
{
  public function authorize(): bool
  {
    return auth()->check()
      && auth()->user()->hasAnyRole([
        UserRoles::SUPPORTER->value,
        UserRoles::LEADER->value,
        UserRoles::ADMIN->value
      ]);
  }

  public function rules(): array
  {
    return [
      'user_id' => 'required',
      'user_id.*' => 'required|string|exists:users,id'
    ];
  }
}
