<?php

namespace App\Console\Commands;

use App\Constants\TicketStatus;
use App\Helpers\MailHelper;
use App\Models\Client as ModelsClient;
use App\Models\TicketEmail;
use App\Models\Ticket;
use App\Services\ClientService;
use App\Services\CommentService;
use App\Services\TicketMailService;
use App\Services\TicketService;
use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FetchSentMails extends Command
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
    protected $signature = 'ticket:fetch-sent-emails {ticketMailId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch sent emails via IMAP and create tickets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching emails...');
        Log::info('YourCommand is running at ' . now());
        $ticketMailId = $this->argument('ticketMailId');
        $IMAP_client = Client::account('default');
        $IMAP_client->connect();

        $fetchTime = Carbon::now()->subMinutes(3);
        $messages = $IMAP_client
          ->getFolder('[Gmail]/Thư đã gửi')
          ->messages()
          ->setFetchFlags(false)
          
          ->since($fetchTime)
          ->get();
        foreach ($messages as $message) {
          if(!array_key_exists("ticket_mail_id", $message->getAttributes())) {
            continue;
          }
          if($message->getAttributes()["ticket_mail_id"] != $ticketMailId) {
            continue;
          }
          $data = $this->mailHelper->processIMAPEmail($message);
          $this->info('process email : ' . $data['message_id']);
          $ticketMail = TicketEmail::where('id', $ticketMailId)->first();
          $ticketMail->update([
            'message_id' => $data['message_id'],
            'in_reply_to' => $data['in_reply_to'],
            'references' => $data['references']
          ]);
          break;
        }

        $IMAP_client->disconnect();

        $this->info('Emails fetched successfully.');
    }

}
