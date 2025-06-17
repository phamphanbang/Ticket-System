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
use Illuminate\Support\Facades\DB;
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
    $this->info('connected');
    $lastUid = DB::table('imap_sync_states')
      ->where('folder', '[Gmail]/Thư đã gửi')
      ->value('last_uid') ?? 0;

    $fetchTime = Carbon::now()->subMinutes(3);
    // $folder = $IMAP_client->getFolder('[Gmail]/Thư đã gửi');
    // $query = $folder->query()
    //   ->setFetchFlags(false)
    //   ->setFetchBody(false);
    // // if ($lastUid > 0) {
    // //   $query->getByUidGreaterOrEqual($lastUid);
    // // }
    // // ->since($fetchTime)

    // $messages = $query->get();
    $messages = $IMAP_client->getFolder('[Gmail]/Thư đã gửi')
      ->messages()
      ->setFetchFlags(false)
      ->setFetchBody(false)
      ->since($fetchTime)
      ->get();
    $this->info('get messages');
    foreach ($messages as $message) {
      $uid = $message->getUid();
      if (!array_key_exists("ticket_mail_id", $message->getAttributes())) {
        continue;
      }
      if ($message->getAttributes()["ticket_mail_id"] != $ticketMailId) {
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
      DB::table('imap_sync_states')->updateOrInsert(
        ['folder' => '[Gmail]/Thư đã gửi'],
        ['last_uid' => $uid, 'updated_at' => now()]
      );
      break;
    }

    $IMAP_client->disconnect();

    $this->info('Emails fetched successfully.');
  }
}
