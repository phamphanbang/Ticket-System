<?php

namespace App\Http\Middleware;

use App\Models\User;
use Auth0\Laravel\Facade\Auth0;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Auth0JWTMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        dd($request->user());
        $token = Auth0::decode($request);
        
        if (! $token?->valid()) {
            return response()->json(['message' => 'Unauthorized: Invalid or missing token'], 401);
        }

        $payload = $token->user ?? [];

        if (!isset($payload['email'], $payload['sub'])) {
            return response()->json(['message' => 'Unauthorized: Missing required user info'], 401);
        }

        $user = User::where('auth0_id', $payload['sub'])->orWhere('email', $payload['email'])->first();

        if (!$user && isset($payload['email'])) {
            $emailParts = explode('@', $payload['email']);
            $payload['name'] = $emailParts[0];
        }

        if (!$user) {
            $user = User::create([
                'name'      => $payload['name'] ?? 'Unknown',
                'email'     => $payload['email'],
                'auth0_id'  => $payload['sub'],
                'avatar'    => $payload['picture'] ?? null,
                'role'      => 'user',
                'password'  => bcrypt(str()->random(32)), 
            ]);
        }

        Auth::login($user); 

        return $next($request);
    }
}
