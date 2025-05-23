<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAssignsTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'assign_to' => 'required|exists:users,id|uuid',
        ];
    }
}
