<?php
// app/Http/Controllers/SlackWebhookController.php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\UserSlackConnection;
use App\Services\SlackService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;

class SlackController extends Controller
{
    use ApiResponse;
    public function __construct(
        protected SlackService $slackService,
    ) {}
    public function sendTestNotification(Ticket $ticket)
    {
        $this->slackService->sendAssignedNotification($ticket);

        return $this->success(
            null,
            'Test notification sent successfully'
        );
    }

    public function handle(Request $request)
    {
        $payload = $request->all();

        // Slack URL verification challenge
        if (isset($payload['type']) && $payload['type'] === 'url_verification') {
            return response($payload['challenge'], 200)->header('Content-Type', 'text/plain');
        }

        // Handle team_join event
        if ($payload['type'] === 'event_callback' && $payload['event']['type'] === 'team_join') {
            $user = $payload['event']['user'];

            // Save user info (example)
            User::updateOrCreate(
                ['slack_user_id' => $user['id']],
                [
                    'name' => $user['profile']['real_name'] ?? '',
                    'email' => $user['profile']['email'] ?? '',
                    'avatar' => $user['profile']['image_192'] ?? '',
                ]
            );
        }

        return response()->json(['ok' => true]);
    }

    public function getOAuthUrl(Request $request)
    {
        $clientId = config('services.slack.client_id');
        $redirectUri = env('FRONTEND_URL') . '/api/slack/callback';
        $scopes = 'chat:write users:read';

        $url = "https://slack.com/oauth/v2/authorize?client_id={$clientId}&scope={$scopes}&redirect_uri={$redirectUri}";

        return $this->success([
            'url' => $url
        ]);
    }

    public function handleCallback(Request $request)
    {
        $response = Http::asForm()->post('https://slack.com/api/oauth.v2.access', [
            'client_id' => config('services.slack.client_id'),
            'client_secret' => config('services.slack.client_secret'),
            'code' => $request->code,
            'redirect_uri' => env('FRONTEND_URL') . '/api/slack/callback',
        ]);

        if (!$response->ok() || !$response->json('ok')) {
            return response()->json([
                'error' => 'Slack authorization failed.',
                'data' => $response->json(),
            ], 400);
        }

        $data = $response->json();
        $user = Auth::user();

        UserSlackConnection::updateOrCreate([
            'user_id' => $user->id,
        ], [
            'slack_user_id' => $data['authed_user']['id'],
            'slack_team_id' => $data['team']['id'],
            'access_token' => Crypt::encryptString($data['access_token']),
            'connected_at' => now(),
            'disconnected_at' => null,
        ]);

        return response()->json(['message' => 'Slack connected successfully.']);
    }

    public function disconnect(Request $request)
    {
        $request->user()->slackConnection()->update([
            'disconnected_at' => now(),
        ]);

        return response()->json(['message' => 'Slack disconnected.']);
    }
}
