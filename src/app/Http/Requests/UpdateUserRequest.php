<?php

namespace App\Http\Requests;

use App\Constants\UserRoles;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'role'     => 'required|string|in:admin,user',
        ];
    }
}
