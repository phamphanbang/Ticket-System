<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GmailService;
use Illuminate\Support\Facades\Log;

class GmailFetch extends Command
{
  protected $signature = 'gmail:fetch';
  protected $description = 'Fetch emails from Gmail';

  public function handle(GmailService $gmail)
  {
    $this->info('Fetching emails...');
    try {
      $query = 'after:1686990000 subject:[ESReport]';
      $messages = $gmail->fetchMessages(1, $query);

      foreach ($messages as $msg) {
        // $this->line("ID: {$msg['id']}");
        // $this->line("Snippet: {$msg['snippet']}");
        // $this->line("Subject: {$msg['subject']}");
        // $this->line(str_repeat('-', 40));
        Log::info('header', $msg['headers']);
        $messageId = null;
        foreach ($msg['headers'] as $header) {
          $this->info($header->name); 
          if ($header->name === 'Message-ID') {
            $messageId = $header->value;
            
            break;
          }
        }
        $this->info($messageId);
      }
    } catch (\Exception $e) {
      $this->error('Error: ' . $e->getMessage());
    }
  }
}
