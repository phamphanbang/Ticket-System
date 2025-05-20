<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $res = $this->authService->login($validated['email'], $validated['password']);
        return response()->success($res['data'], $res['message']);
    }

    public function logout(Request $request): JsonResponse
    {
        $res = $this->authService->logout($request->user());
        return response()->success($res['data'], $res['message']);
    }
}
