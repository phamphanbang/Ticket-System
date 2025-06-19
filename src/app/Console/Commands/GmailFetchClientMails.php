<?php

namespace App\Console\Commands;

use App\Constants\TicketStatus;
use App\Events\MailCreated;
use App\Helpers\MailHelper;
use App\Jobs\NotifyTicketHasBeenCreated;
use App\Models\Client as ModelsClient;
use App\Models\TicketEmail;
use App\Models\Ticket;
use App\Services\ClientService;
use App\Services\CommentService;
use App\Services\GmailService;
use App\Services\TicketMailService;
use App\Services\TicketService;
use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GmailFetchClientMails extends Command
{
  public function __construct(
    protected GmailService $gmailService,
    protected TicketService $ticketService,
  ) {
    parent::__construct();
  }
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'gmail:fetch-client-mails';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Fetch emails via gmail and create tickets';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $this->info('Fetching emails...');
    Log::info('YourCommand is running at ' . now());


    $fetchTime = Carbon::now()->subHour();
    $query = 'after:1686990000 category:primary';
    $folder = 'INBOX';
    $messages = $this->gmailService->fetchMessages(20, $query, $folder);

    foreach ($messages as $message) {
      
      $data = [];
      $data['body'] =$this->extractReplyFromEmail($message['body']);
      foreach ($message['headers'] as $header) {
        if ($header->name === 'In-Reply-To') {
          $data['in_reply_to'] = $header->value;
        }
        if ($header->name === 'Subject') {
          $data['subject'] = $header->value;
        }
        if ($header->name === 'References') {
          $data['references'] = $header->value;
        }
        if ($header->name === 'Message-ID') {
          $data['message_id'] = $header->value;
        }
        if ($header->name === 'From') {
          $data['from_email'] = $this->getMail($header->value);
          list($username, $domain) = explode('@', $data['from_email']);
          $data['from_name'] = $username;
        }
        // if ($header->name === 'Personal') {
        //   $data['from_name'] = $header->value;
        // }
        if ($header->name === 'To') {
          $data['to_email'] = $this->getMail($header->value);
        }
        if ($header->name === 'Date') {
          $data['created_at'] = $header->value;
        }
      }
      $hasTicketSubject = str_contains($data['subject'], '[ESReport]');
      $isReply = array_key_exists('in_reply_to', $data) && $data['in_reply_to'];
      if (!$isReply && !$hasTicketSubject) {
        continue;
      }
      $this->info('process email : ' . $data['message_id']);
      $mail = TicketEmail::where('message_id', $data['message_id'])->first();
      if ($mail) {
        continue;
      }

      if (!$isReply) {
        $this->info('create ticket : ' . $data['message_id']);
        $this->handleNewTicket($data, $message);
        continue;
      }

      $this->info('reply ticket : ' . $data['message_id']);
      $this->handleReply($data, $message);
      // $message->setFlag('Seen');
    }

    $this->info('Emails fetched successfully.');
  }

  private function handleReply($data, $message): void
  {
    $this->info($data['references']);
    $replyTarget = TicketEmail::whereIn('message_id', $this->getReference($data['references']))
      ->orWhere('message_id', $data['in_reply_to'])
      ->latest()
      ->first();
    if (!$replyTarget) return;
    $ticket = $replyTarget->ticket;
    if ($ticket->status === TicketStatus::COMPLETE->value || $ticket->status === TicketStatus::ARCHIVED->value) {
      return;
    }
    try {
    $ticket = $replyTarget->ticket;
    $this->info('reply ticket : ' . $ticket->subject);
    $receivedEmail = $ticket->ticketEmails()->create([
      'message_id' => $data['message_id'],
      'in_reply_to' => array_key_exists('in_reply_to', $data) ? $data['in_reply_to'] : null,
      'references' => array_key_exists('references', $data) ? json_encode($data['references']) : null,
      'from_email' => $data['from_email'],
      'from_name' => $data['from_name'],
      'to_email' => $data['to_email'],
      'subject' => $data['subject'],
      'body' => $data['body'],
      'received_at' => Carbon::now(),
      'created_at' => $data['created_at']
    ]);
    event(new MailCreated($receivedEmail)); 
    // $this->handleAttachments($receivedEmail, $message);
    $this->info("Reply added to ticket ID {$ticket->id}");
    Log::info("Reply processed for ticket ID {$ticket->id}");
    } catch (\Exception $e) {
      $this->error('Error: ' . $e->getMessage());
    }
  }

  private function handleNewTicket($data, $message): void
  {
    $this->info('create ticket : ' . $data['message_id']);
    $ticket_data = [
      'client_email' => $data['from_email'],
      'title' => $data['subject'],
      'description' => $data['body'],
    ];
    $shouldSendEmail = false;
    $ticket = $this->ticketService->store($ticket_data, $shouldSendEmail);
    $mail = $ticket->ticketEmails()->create([
      'message_id' => $data['message_id'],
      'in_reply_to' => array_key_exists('in_reply_to', $data) ? $data['in_reply_to'] : null,
      'references' => array_key_exists('references', $data) ? json_encode($data['references']) : null,
      'from_email' => $data['from_email'],
      'from_name' => $data['from_name'],
      'to_email' => $data['to_email'],
      'subject' => $data['subject'],
      'body' => $data['body'],
      'received_at' => Carbon::now(),
      'created_at' => $data['created_at']
    ]);
    NotifyTicketHasBeenCreated::dispatch($ticket);
    $this->handleAttachments($mail, $message);
    $this->info('Ticket' . $ticket->subject . ' created successfully.');
  }

  private function handleAttachments(TicketEmail $email, $message): void
  {
    foreach ($message['attachments'] as $attachment) {
      Log::info('attachment : ', $attachment);
      $filename = uniqid() . '_' . $attachment['file_name'];
      $this->info('process attachment : ' . $filename);

      $fileExtension = $attachment['file_extension'];
      $contentType = $attachment['content_type'];
      

      $relativePath = "tickets/{$email->ticket_id}";
      $storagePath = storage_path("app/private/{$relativePath}");
      if (!Storage::disk('local')->exists($relativePath)) {
        Storage::disk('local')->makeDirectory($relativePath);
      }
      file_put_contents($storagePath . '/' . $filename, $attachment['data']);
      $fileSize = filesize($storagePath);
      // $attachment->save($storagePath, $filename);
      $email->attachments()->create([
        'ticket_id' => $email->ticket_id,
        'file_name' => $filename,
        'file_extension' => $fileExtension,
        'file_path' => $relativePath,
        'file_size' => $fileSize,
        'content_type' => $contentType
      ]);
    }
  }

  private function getMail($fullAddress) {
    preg_match('/<(.+)>/', $fullAddress, $matches);
    return $matches[1] ?? $fullAddress;
  }

  private function getReference($references) {
    preg_match_all('/<([^>]+)>/', $references, $matches);
    return $matches[1];
  }

  function extractReplyFromEmail($body)
  {
    $pattern = '/^(.*?)(?=^Vào .+ viết:|^On .+ wrote:|^From:)/msu';

    if (preg_match($pattern, $body, $matches)) {
        return trim($matches[1]);
    }

    return trim($body);
  }
}
