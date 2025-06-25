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

  public function sendCompletedNotification(Ticket $ticket): void
  {
    $username = $ticket->holder->name;
    $ticketTitle = $ticket->title;
    $ticketUrl = env('FRONTEND_URL') . '/tickets/' . $ticket->id;

    $blocks = [
      [
        "type" => "section",
        "text" => [
          "type" => "mrkdwn",
          "text" => "✅ Hey, *{$username}*!\nYour ticket has been *completed*:\n>*{$ticketTitle}*"
        ]
      ],
      [
        "type" => "context",
        "elements" => [
          [
            "type" => "mrkdwn",
            "text" => "You can review the completed ticket in the Ticket app."
          ]
        ]
      ],
      [
        "type" => "actions",
        "elements" => [
          [
            "type" => "button",
            "text" => [
              "type" => "plain_text",
              "text" => "📄 View Completed Ticket",
              "emoji" => true
            ],
            "style" => "primary",
            "url" => $ticketUrl
          ]
        ]
      ]
    ];

    $this->sendSlackNotify($ticket->holder->slackConnection, $blocks);
  }

  public function sendNewTicketNotification(Ticket $ticket): void
  {
    $username = $ticket->holder->name;
    $ticketTitle = $ticket->title;
    $ticketUrl = env('FRONTEND_URL') . '/tickets/' . $ticket->id;

    $blocks = [
      [
        "type" => "section",
        "text" => [
          "type" => "mrkdwn",
          "text" => "✅ Hey, *{$username}*!\nA new ticket has been created:\n>*{$ticketTitle}*"
        ]
      ],
      [
        "type" => "context",
        "elements" => [
          [
            "type" => "mrkdwn",
            "text" => "You can review the new ticket in the Ticket app."
          ]
        ]
      ],
      [
        "type" => "actions",
        "elements" => [
          [
            "type" => "button",
            "text" => [
              "type" => "plain_text",
              "text" => "📄 View New Ticket",
              "emoji" => true
            ],
            "style" => "primary",
            "url" => $ticketUrl
          ]
        ]
      ]
    ];

    $this->sendSlackNotify($ticket->holder->slackConnection, $blocks);
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
