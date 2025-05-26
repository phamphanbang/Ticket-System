<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255|min:5',
            'description' => 'required|string',
            'assign_to' => 'nullable|exists:users,id|uuid',
        ];
    }
}
