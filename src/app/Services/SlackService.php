<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\UserSlackConnection;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackService
{
  public function fetchUsers()
  {
    $response = Http::withToken(env('SLACK_BOT_USER_OAUTH_TOKEN'))
      ->get('https://slack.com/api/users.list');

    if (!$response->ok() || !$response['ok']) {
      throw new Exception('Failed to fetch users from Slack');
    }

    return $response['members'];
  }

  public function sendSlackNotify(UserSlackConnection $connection, array $blocks)
  {
    if (!$connection->slack_channel_id) {
      $dmResponse = Http::withToken(env('SLACK_BOT_USER_OAUTH_TOKEN'))
        ->post('https://slack.com/api/conversations.open', [
          'users' => $connection->slack_user_id
        ]);

      $channelId = $dmResponse->json('channel.id');
      $connection->update([
        'slack_channel_id' => $channelId
      ]);
    }

    $sendResponse = Http::withToken(env('SLACK_BOT_USER_OAUTH_TOKEN'))
      ->post('https://slack.com/api/chat.postMessage', [
        'channel' => $connection->slack_channel_id,
        'blocks' => $blocks
      ]);
  }
}
