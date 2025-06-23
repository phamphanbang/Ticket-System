<?php

namespace App\Console\Commands;

use App\Models\TicketEmail;
use Illuminate\Console\Command;
use App\Services\GmailService;
use Illuminate\Support\Facades\Log;

class GmailFetch extends Command
{
  protected $signature = 'gmail:fetch {ticketMailId}';
  protected $description = 'Fetch emails from Gmail';

  public function handle(GmailService $gmail)
  { 
    $this->info('Fetching emails...');
    try {
      $query = 'after:1686990000';
      $ticketMailId = $this->argument('ticketMailId');
      $folder = 'SENT';
      $messages = $gmail->fetchMessages(10, $query, $folder);

      foreach ($messages as $msg) {
        Log::info('header', $msg['headers']);
        Log::info($msg['body']);
        // Log::info('attachments', $msg['attachments']);
        $messageId = null;
        $inReplyTo = null;
        $references = null;
        $hasTicketMailId = false;
        foreach ($msg['headers'] as $header) { 
          if ($header->name === 'Message-ID') {
            $messageId = $header->value;
          }
          if ($header->name === 'In-Reply-To') {
            $inReplyTo = $header->value;
          }
          if ($header->name === 'References') {
            $references = $header->value;
          }
          if($header->name === 'Ticket-Mail-Id' && $header->value === $ticketMailId) {
            $hasTicketMailId = true;
          }
        }
        // Log::info('hasTicketMailId', $hasTicketMailId);
        if($hasTicketMailId) {
          Log::info('update ticket mail : ' . $ticketMailId);
          // Log::info( $messageId);
          // Log::info( $inReplyTo);
          // Log::info( $references);
          $ticketMail = TicketEmail::where('id', $ticketMailId)->first();
          Log::info('updated ticket mail : '. $ticketMail->id);
          $ticketMail->update([
            'message_id' => $messageId,
            'in_reply_to' => $inReplyTo,
            'references' => json_encode($references)
          ]);
          
        }
      }
    } catch (\Exception $e) {
      $this->error('Error: ' . $e->getMessage());
    }
  }
}
