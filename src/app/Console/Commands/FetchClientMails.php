<?php

namespace App\Console\Commands;

use App\Constants\TicketStatus;
use App\Models\Ticket;
use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use Carbon\Carbon;

class FetchClientMails extends Command
{
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
        $client = Client::account('default');
        $client->connect();

        $oneHourAgo = Carbon::now()->subHour();
        $messages = $client->getFolder('INBOX')->messages()->since($oneHourAgo)->unseen()->get();

        foreach ($messages as $message) {
            $from = $message->getFrom()[0];
            $client_email = $from->mail;
            $client_name = $from->personal ?: 'Unknown Client';
            $subject = $message->getSubject();
            $htmlBody = $message->getHTMLBody();
            $body = $message->getTextBody() ?: strip_tags($htmlBody);

            Ticket::create([
                'title' => $subject,
                'description' => $body,
                'client_email' => $client_email,
                'client_name' => $client_name,
                'raw_email' => $htmlBody,
                'status' => TicketStatus::New->value,
                'deadline' => now()->addDays(7),
            ]);

            $this->info("Ticket created for $client_name <$client_email>");

            $message->setFlag('Seen');
        }

        $client->disconnect();
    }
}
