<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Models\TicketAuditLog;
use App\Models\User;
use App\Services\SlackService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotifyClientReplyEmail implements ShouldQueue
{
  use Queueable, InteractsWithQueue, SerializesModels, Dispatchable;

  public $slackService;
  /**
   * Create a new job instance.
   */
  public function __construct(
    protected Ticket $ticket,
  ) {
    $this->slackService = app(SlackService::class);
  }

  /**
   * Execute the job.
   */
  public function handle(): void
  {
    $this->sendNotifyToStaff();
  }

  protected function sendNotifyToStaff()
  {
    $staff = $this->ticket->staff;
    if (!$staff || !$staff->isSlackConnected()) return;
    $slackUserId = $staff->slackConnection->slack_user_id;
    $ticketUrl = $this->ticket->ticketUrl();

    $blocks = [
      [
        "type" => "section",
        "text" => [
          "type" => "mrkdwn",
          "text" => "✉️ *Client Reply Received: {$this->ticket->title}*\n"
            . "Hey, <@$slackUserId>! the client has just replied to ticket *#{$this->ticket->id}* via email.\n"
            . "👤 *Client:* {$this->ticket->client->name}\n"
            . "Please check and respond accordingly."
        ]
      ],
      [
        "type" => "actions",
        "elements" => [
          [
            "type" => "button",
            "text" => [
              "type" => "plain_text",
              "text" => "📄 View Ticket Thread",
              "emoji" => true
            ],
            "style" => "primary",
            "url" => $ticketUrl
          ]
        ]
      ]
    ];

    $this->slackService->sendSlackNotify($staff->slackConnection, $blocks);
  }
}
