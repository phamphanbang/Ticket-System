<?php

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;

class AuthService
{
  public function login(string $email, string $password)
  {
    $user = User::where('email', $email)->first();

    if (!$user || !Hash::check($password, $user->password)) {
      throw new AuthenticationException(__('messages.invalid_credentials'));
    }

    if ($user->tokens()) {
      $user->tokens()->delete();
    }

    $token = $user->createToken('accessToken', ['*'], now()->addHours(8))->plainTextToken;

    return [
      'message' => 'Login successful',
      'data' => [
        'user' => new UserResource($user),
        'token' => $token
      ]
    ];
  }

  public function logout(User $user)
  {
    $user->tokens()->delete();
    return [
      'message' => 'Logout successful',
      'data' => []
    ];
  }

  public function me(User $user): array
  {
    return [
      'message' => 'User retrieved successfully',
      'data' => [
        'user' => new UserResource($user)
      ]
    ];
  }
}
