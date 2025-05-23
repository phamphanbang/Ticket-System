<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActionTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:2,3,4'
        ];
    }
}
