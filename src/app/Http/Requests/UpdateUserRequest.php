<?php

namespace App\Http\Requests;

use App\Constants\UserRoles;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $userId,
            'password' => 'nullable|string|min:8|confirmed',
            'role'     => 'required|string|in:admin,staff,supporter,leader' ,
        ];
    }
}
