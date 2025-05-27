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
            $data = $this->ticketMailService->processIMAPEmail($message);

            $ticket_client = $this->clientService->createClient([
                'name' => $data['from_name'],
                'email' => $data['from_email'],
            ]);

            $ticket = Ticket::create([
                'title' => $data['subject'],
                'description' => $data['body'],
                'client' => $ticket_client->id,
                'status' => TicketStatus::New->value,
                'deadline' => now()->addDays(7),
            ]);

            $mail = $this->ticketMailService->createTicketMail([
                ...$data,
                'ticket_id' => $ticket->id,
            ]);

            $ticket->created_mail_id = $mail->id;
            $ticket->save();

            $this->info("Ticket created for $ticket_client->name <$ticket_client->email> with ID: $ticket->id");

            $message->setFlag('Seen');
        }

        $IMAP_client->disconnect();
        Log::info('YourCommand is running at ' . now());

        $this->info('Emails fetched successfully.');
    }
}
