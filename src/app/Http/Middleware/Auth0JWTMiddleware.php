<?php

namespace App\Http\Middleware;

use App\Models\User;
use Auth0\Laravel\Entities\CredentialEntity;
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
        $userInfo = $request->user();
        $email = $userInfo->getAttribute(env('AUTH0_CUSTOM_DOMAIN').'email');
        $name = $userInfo->getAttribute(env('AUTH0_CUSTOM_DOMAIN').'name');
        $auth0_id = $userInfo->getAttribute('sub');

        $user = User::where('auth0_id', $auth0_id)->orWhere('email', $email)->first();
        
        if (!$user) {
            $user = User::create([
                'name'      => $name,
                'email'     => $email,
                'auth0_id'  => $auth0_id,
                'avatar'    => $userInfo->getAttribute(env('AUTH0_CUSTOM_DOMAIN').'picture') ?? null,
                'role'      => 'user',
                'password'  => bcrypt(str()->random(32)), 
            ]);
        }
        $credential = CredentialEntity::create($user);
        Auth::login($credential); 

        return $next($request);
    }
}
