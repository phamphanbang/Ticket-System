<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\SlackService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotifyTicketHasBeenCompleted implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels, Dispatchable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Ticket $ticket
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        // app(SlackService::class)->sendCompletedNotification($this->ticket);
        $holder = $this->ticket->holder;
        $staffName = $this->ticket->staff ? $this->ticket->staff->name : 'Unassigned';
        if (!$holder->isSlackConnected()) return;
        $connection = $holder->slackConnection;
        $ticketTitle = $this->ticket->title;
        $ticketUrl = env('FRONTEND_URL') . '/tickets/' . $this->ticket->id;

        // $blocks = [
        //     [
        //         "type" => "section",
        //         "text" => [
        //             "type" => "mrkdwn",
        //             "text" => "*✅ Ticket Completed: {$ticketTitle}*"
        //         ]
        //     ],
        //     [
        //         "type" => "section",
        //         "text" => [
        //             "type" => "mrkdwn",
        //             "text" => "Hey, *{$holder->name}*! A ticket has just been marked as *completed*."
        //         ]
        //     ],
        //     [
        //         "type" => "section",
        //         "fields" => [
        //             [
        //                 "type" => "mrkdwn",
        //                 "text" => "*👤 Client:*\n{$this->ticket->client->name}"
        //             ],
        //             [
        //                 "type" => "mrkdwn",
        //                 "text" => "*🧑‍💼 Assigned To:*\n{$holder->name}"
        //             ]
        //         ]
        //     ],
        //     [
        //         "type" => "context",
        //         "elements" => [
        //             [
        //                 "type" => "mrkdwn",
        //                 "text" => "You can review the completed ticket in the Ticket app."
        //             ]
        //         ]
        //     ],
        //     [
        //         "type" => "actions",
        //         "elements" => [
        //             [
        //                 "type" => "button",
        //                 "text" => [
        //                     "type" => "plain_text",
        //                     "text" => "📄 View Completed Ticket",
        //                     "emoji" => true
        //                 ],
        //                 "style" => "primary",
        //                 "url" => $ticketUrl
        //             ]
        //         ]
        //     ]
        // ];
        $blocks = [
            [
                "type" => "section",
                "text" => [
                    "type" => "mrkdwn",
                    "text" => "✅ *Ticket Completed: {$ticketTitle}*\n"
                        . "Hey, *{$holder->name}*! A ticket has just been marked as *completed*.\n"
                        . " *Id:* {$this->ticket->id}\n"
                        . "👤 *Client:* {$this->ticket->client->name}\n"
                        . "🧑‍💼 *Assigned To:* {$staffName}\n"
                        . "You can review the completed ticket in the Ticket app."
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
