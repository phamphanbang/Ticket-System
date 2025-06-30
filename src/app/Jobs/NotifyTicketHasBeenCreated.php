<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\SlackService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyTicketHasBeenCreated implements ShouldQueue
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
        $this->sendNotifyToHolder();
    }

    protected function sendNotifyToHolder()
    {
        $holder = $this->ticket->holder;
        if (!$holder->isSlackConnected()) return;
        $slackUserId = $holder->slackConnection->slack_user_id;
        $ticketUrl = $this->ticket->ticketUrl();

        $blocks = [
            [
                "type" => "section",
                "text" => [
                    "type" => "mrkdwn",
                    "text" => "📬 *New Ticket Created via Email: {$this->ticket->title}*\n"
                        . "Hey, <@$slackUserId>! a new ticket has been automatically created from a client's email.\n"
                        . "👤 *Client:* {$this->ticket->client->name}\n"
                        . "✉️ *Ticket title:* {$this->ticket->title}\n"
                        . "You’ve been assigned to handle this ticket."
                ]
            ],
            [
                "type" => "actions",
                "elements" => [
                    [
                        "type" => "button",
                        "text" => [
                            "type" => "plain_text",
                            "text" => "📄 View Ticket",
                            "emoji" => true
                        ],
                        "style" => "primary",
                        "url" => $ticketUrl
                    ]
                ]
            ]
        ];

        $this->slackService->sendSlackNotify($holder->slackConnection, $blocks);
    }
}
