<?php
// app/Http/Controllers/SlackWebhookController.php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Services\SlackService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SlackWebhookController extends Controller
{
    use ApiResponse;
    public function __construct(
        protected SlackService $slackService,
    ) {}
    public function sendTestNotification(Ticket $ticket)
    {
        $this->slackService->sendTestNotification($ticket);

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
            return response($payload['challenge'], 200);
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
}
