<?php

namespace App\Http\Requests;

use App\Constants\TicketStatus;
use App\Constants\UserRoles;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255|min:5',
            'description' => 'sometimes|string',
            'status' => 'sometimes|string|in:' . implode(',', TicketStatus::all()),
            'staff_id' => 'sometimes|uuid|exists:users,id',
        ];
    }
}
