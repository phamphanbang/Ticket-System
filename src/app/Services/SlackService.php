<?php 

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\Http;

class SlackService
{
  public function sendTestNotification(Ticket $ticket): void
  {
    $username = $ticket->staff->name;
    $ticketTitle = $ticket->title;
    $ticketUrl = env('FRONTEND_URL') . '/tickets/' . $ticket->id;

    $blocks = [
      [
        "type" => "section",
        "text" => [
          "type" => "mrkdwn",
          "text" => "👋 Hey, *{$username}*!\nYou have been assigned to a new ticket:\n>*{$ticketTitle}*"
        ]
      ],
      [
        "type" => "context",
        "elements" => [
          [
            "type" => "mrkdwn",
            "text" => "Please go to the Ticket app to check your assignment."
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
              "text" => "🔍 View Ticket",
              "emoji" => true
            ],
            "style" => "primary",
            "url" => $ticketUrl
          ]
        ]
      ]
    ];

    $dmResponse = Http::withToken(env('SLACK_BOT_USER_OAUTH_TOKEN'))
      ->post('https://slack.com/api/conversations.open', [
        'users' => $ticket->staff->slack_user_id
      ]);

    $channelId = $dmResponse->json('channel.id');

    $sendResponse = Http::withToken(env('SLACK_BOT_USER_OAUTH_TOKEN'))
      ->post('https://slack.com/api/chat.postMessage', [
        'channel' => $channelId,
        'blocks' => $blocks
      ]);
  }
}
