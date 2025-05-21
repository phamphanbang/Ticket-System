<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminCreateTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'assign_to' => 'nullable|exists:users,id|uuid',
            'title' => 'required|string|max:255|min:5',
            'description' => 'required|string',
            'priority' => 'required|in:1,2,3', 
            'client_email' => 'required|email|max:255',
            'client_name' => 'required|string|max:255',
            'deadline' => 'required|date|after:today',
        ];
    }
}
