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

class NotifyStaffHasBeenAssigned implements ShouldQueue
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

    protected function sendNotifyToHolder()
    {
        $holder = $this->ticket->holder;
        if (!$holder->isSlackConnected()) return;
        $slackUserId = $holder->slackConnection->slack_user_id;
        $currentStaff = $this->ticket->staff;
        $currentStaffDisplay = '';
        if($currentStaff->isSlackConnected()) {
            $currentStaffDisplay = "<@{$currentStaff->slackConnection->slack_user_id}>";
        } else {
            $currentStaffDisplay = $currentStaff->name ?? 'Unassigned';
        }
        $ticketUrl = $this->ticket->ticketUrl();

        $blocks = [
            [
                "type" => "section",
                "text" => [
                    "type" => "mrkdwn",
                    "text" => "🔄 *Ticket Reassigned: {$this->ticket->title}*\n"
                        . "Heads up, <@$slackUserId>! Your ticket has been *reassigned* to a new staff member.\n"
                        . ":id: *Id:* {$this->ticket->id}\n"
                        . "👤 *Client:* {$this->ticket->client->name}\n"
                        . "🧑‍💼 *New Assignee:* {$currentStaffDisplay}\n"
                        . "Please stay updated and coordinate if needed."
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

        $this->slackService->sendSlackNotify($holder->slackConnection, $blocks);
    }

    protected function sendNotifyToStaff()
    {
        $staffIds = TicketAuditLog::select('staff_id')->where('ticket_id', $this->ticket->id)->distinct()->pluck('staff_id');
        $staffs = User::whereIn('id', $staffIds)->get();
        $currentStaff = $this->ticket->staff;
        $currentStaffDisplay = '';
        if($currentStaff->isSlackConnected()) {
            $currentStaffDisplay = "<@{$currentStaff->slackConnection->slack_user_id}>";
        } else {
            $currentStaffDisplay = $currentStaff->name ?? 'Unassigned';
        }

        foreach ($staffs as $staff) {
            if (!$staff->isSlackConnected()) continue;
            $ticketUrl = $this->ticket->ticketUrl();

            $blocks = [
                [
                    "type" => "section",
                    "text" => [
                        "type" => "mrkdwn",
                        "text" => "🔄 *Ticket Reassigned: {$this->ticket->title}*\n"
                            . "Heads up, team! This ticket has been *reassigned* to a new staff member.\n"
                            . ":id: *Id:* {$this->ticket->id}\n"
                            . "👤 *Client:* {$this->ticket->client->name}\n"
                            . "🧑‍💼 *New Assignee:* {$currentStaffDisplay}\n"
                            . "Please stay updated and coordinate if needed."
                    ]
                ],
                [
                    "type" => "actions",
                    "elements" => [
                        [
                            "type" => "button",
                            "text" => [
                                "type" => "plain_text",
                                "text" => "📄 View Ticket Details",
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
}
