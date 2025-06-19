<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $res = $this->authService->login($validated['email'], $validated['password']);
        return $this->success($res['data'], $res['message']);
    }

    public function logout(Request $request): JsonResponse
    {
        $res = $this->authService->logout($request->user());
        return $this->success($res['data'], $res['message']);
    }

    public function me(Request $request): JsonResponse
    {
        $res = $this->authService->me($request->user());
        return $this->success($res['data'], $res['message']);
    }
}
