<?php

namespace App\Console\Commands;

use App\Constants\TicketStatus;
use App\Models\Ticket;
use App\Services\ClientService;
use App\Services\TicketMailService;
use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FetchClientMails extends Command
{
    public function __construct(
        protected ClientService $clientService,
        protected TicketMailService $ticketMailService
    ){
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
            $from = $message->getFrom()[0];
            $messageId = $message->getMessageId();
            $in_reply_to = $message->getInReplyTo();
            $references = $message->getReferences() ?? [];
            dump($references);
            $raw_email = $message->getRawContent();
            $client_email = $from->mail;
            $client_name = $from->personal ?: 'Unknown Client';
            $subject = $message->getSubject();
            $htmlBody = $message->getHTMLBody();
            $body = $message->getTextBody() ?: strip_tags($htmlBody);
            $parse_email = $message->getTextBody() ?: strip_tags($htmlBody);

            $ticket_client = $this->clientService->createClient([
                'name' => $client_name,
                'email' => $client_email,
            ]);

            $ticket = Ticket::create([
                'title' => $subject,
                'description' => $body,
                'client' => $ticket_client->id,
                'status' => TicketStatus::New->value,
                'deadline' => now()->addDays(7),
            ]);

            $mail = $this->ticketMailService->createTicketMail([
                'ticket_id' => $ticket->id,
                'message_id' => $messageId,
                'from_email' => $client_email,
                'from_name' => $client_name,
                'in_reply_to' => $in_reply_to,
                'subject' => $subject,
                'raw_email' => $raw_email,
                'parse_email' => $parse_email,
                'references' => $references,
            ]);

            $ticket->created_mail_id = $mail->id;
            $ticket->save();

            $this->info("Ticket created for $client_name <$client_email>");

            $message->setFlag('Seen');
        }

        $IMAP_client->disconnect();
        Log::info('YourCommand is running at ' . now());

        $this->info('Emails fetched successfully.');
    }
}
