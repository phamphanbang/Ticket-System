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
        $email = $userInfo->getAttribute('http://ES-ticket-app.com/email');
        $name = $userInfo->getAttribute('http://ES-ticket-app.com/name');
        $auth0_id = $userInfo->getAttribute('sub');
        $slack_user_id = null;
        if ($userInfo->getAttribute('http://ES-ticket-app.com/slack_user_id')) {
            $slack_id_parts = explode('|', $userInfo->getAttribute('http://ES-ticket-app.com/slack_user_id'));
            $slack_user_id = explode('-', end($slack_id_parts))[1];
        }

        $user = User::where('auth0_id', $auth0_id)->orWhere('email', $email)->first();


        $user = User::updateOrCreate([
            'email'     => $email,
        ], [
            'name'      => $name,
            'email'     => $email,
            'auth0_id'  => $auth0_id,
            'avatar'    => $userInfo->getAttribute(env('AUTH0_CUSTOM_DOMAIN') . 'picture') ?? null,
            'role'      => $user->role ?? 'user',
            'password'  => bcrypt(str()->random(32)),
            'slack_user_id' => $slack_user_id,
        ]);

        $credential = CredentialEntity::create($user);
        Auth::login($credential);

        return $next($request);
    }
}
