<?php

namespace App\Console\Commands;

use App\Constants\TicketStatus;
use App\Helpers\MailHelper;
use App\Models\Client as ModelsClient;
use App\Models\ReceivedEmail;
use App\Models\Ticket;
use App\Services\ClientService;
use App\Services\CommentService;
use App\Services\TicketMailService;
use App\Services\TicketService;
use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FetchClientMails extends Command
{
    public function __construct(
        protected ClientService $clientService,
        protected MailHelper $mailHelper,
        protected TicketService $ticketService,
        protected CommentService $commentService
    ) {
        parent::__construct();
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ticket:fetch-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch emails via IMAP and create tickets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching emails...');
        Log::info('YourCommand is running at ' . now());
        $IMAP_client = Client::account('default');
        $IMAP_client->connect();

        $oneHourAgo = Carbon::now()->subHour();
        $messages = $IMAP_client->getFolder('INBOX')->messages()->since($oneHourAgo)->unseen()->get();

        foreach ($messages as $message) {
            $message->setFlag('Seen');
            $data = $this->mailHelper->processIMAPEmail($message);

            if (!$data['in_reply_to'] && !str_contains($data['subject'], '[ESReport]')) {
                continue;
            }

            if (!$data['in_reply_to']) {
                $ticket_data = [
                    'client_email' => $data['from_email'],
                    'subject' => $data['subject'],
                    'description' => $data['body'],
                ];
                $ticket = $this->ticketService->store($ticket_data);
                $ticket->receivedEmails()->create([
                    'message_id' => $data['message_id'],
                    'in_reply_to' => $data['in_reply_to'],
                    'from_email' => $data['from_email'],
                    'to_email' => $data['to_email'],
                    'subject' => $data['subject'],
                    'body' => $data['body'],
                    'attachments' => null,
                    'type' => 'new_ticket',
                    'status' => 'pending',
                    'received_at' => Carbon::now(),
                ]);
                $this->info('Ticket' . $ticket->subject . ' created successfully.');
                Log::info('fetch ticket ' . $ticket->subject);
                continue;
            }
            $message->setFlag('Seen');
        }

        $IMAP_client->disconnect();

        $this->info('Emails fetched successfully.');
    }
}
