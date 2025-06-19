<?php

namespace App\Http\Middleware;

use App\Models\User;
use Auth0\Laravel\Entities\CredentialEntity;
use Closure;
use Exception;
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
        // dd($userInfo);
        if (!$userInfo) {
            throw new Exception('User not found', Response::HTTP_NOT_FOUND);
        }
        $key = env('AUTH0_TOKEN_KEY');
        $email = $userInfo->getAttribute($key . 'email');
        $name = $userInfo->getAttribute($key . 'name');
        $auth0_id = $userInfo->getAttribute('sub');

        $user = User::where('auth0_id', $auth0_id)->orWhere('email', $email)->first();
        if (!$user) {
            throw new Exception('User not found', Response::HTTP_NOT_FOUND);
        }
        $data = [
            'name'      => $name,
            'email'     => $email,
            'auth0_id'  => $auth0_id,
            'avatar'    => $userInfo->getAttribute(env('AUTH0_CUSTOM_DOMAIN') . 'picture') ?? null,
            'role'      => $user->role ?? 'user',
            'password'  => bcrypt(str()->random(32)),
        ];
        if (array_key_exists($key . 'slack_user_id', $userInfo->getAttributes()) && !empty($userInfo->getAttributes($key . 'slack_user_id'))) {
            $slack_id_parts = explode('|', $userInfo->getAttribute($key . 'slack_user_id'));
            $data['slack_user_id'] = explode('-', end($slack_id_parts))[1];
        }

        $user->update($data);

        $credential = CredentialEntity::create($user);
        Auth::login($credential);

        return $next($request);
    }
}
