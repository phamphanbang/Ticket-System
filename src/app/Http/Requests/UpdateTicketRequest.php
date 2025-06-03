<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['admin', 'supporter', 'leader']);
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
