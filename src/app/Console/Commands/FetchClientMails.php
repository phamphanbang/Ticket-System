<?php

namespace App\Console\Commands;

use App\Constants\TicketStatus;
use App\Models\Client as ModelsClient;
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
        protected TicketMailService $ticketMailService,
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
        $IMAP_client = Client::account('default');
        $IMAP_client->connect();

        $oneHourAgo = Carbon::now()->subHour();
        $messages = $IMAP_client->getFolder('INBOX')->messages()->since($oneHourAgo)->unseen()->get();

        foreach ($messages as $message) {
            $message->setFlag('Seen');
            $check = $this->ticketMailService->findByMessageId($message->getMessageId());
            if ($check) continue;
            $data = $this->ticketMailService->processIMAPEmail($message);

            $ticket_client = $this->clientService->createClient([
                'name' => $data['from_name'],
                'email' => $data['from_email'],
            ]);

            if (!$data['in_reply_to']) {
                $ticket = $this->ticketService->createTicketFromMail($data, $ticket_client);
                continue;
            }

            $parentMail = $this->ticketMailService->findByMessageId($data['in_reply_to']);
            if (!$parentMail) continue;
            $ticket = $this->ticketService->getTicketById($parentMail->ticket_id);
            if (!$ticket) continue;
            $mail = $this->ticketMailService->createTicketMail([
                ...$data,
                'ticket_id' => $ticket->id,
            ]);
            $comment = $this->commentService->createComment([
                'ticket_id' => $ticket->id,
                'user_id' => $ticket_client->id,
                'user_type' => ModelsClient::class,
                'mail_id' => $mail->id,
                'body' => $data['body'],
            ]);

            $message->setFlag('Seen');
        }

        $IMAP_client->disconnect();
        Log::info('YourCommand is running at ' . now());

        $this->info('Emails fetched successfully.');
    }
}
