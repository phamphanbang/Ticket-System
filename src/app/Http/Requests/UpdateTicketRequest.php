<?php

namespace App\Http\Requests;

use App\Constants\UserRoles;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check()
            && auth()->user()->hasRole([
                UserRoles::ADMIN->value,
                UserRoles::SUPPORTER->value,
                UserRoles::LEADER->value
            ]);
    }

    public function rules(): array
    {
        return [
            'subject' => 'required|string|max:255|min:5',
            'description' => 'required|string',
            'client_email' => 'required|email',
        ];
    }
}
