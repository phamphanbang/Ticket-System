<?php 

namespace App\Services;

use App\Models\TicketMail;

class TicketMailService
{
  public function createTicketMail($data)
  {
    $mail = TicketMail::create([
      'ticket_id' => $data['ticket_id'],
      'message_id' => $data['message_id'],
      'from_email' => $data['from_email'],
      'from_name' => $data['from_name'],
      'in_reply_to' => $data['in_reply_to'] ?? null,
      'subject' => $data['subject'],
      'raw_email' => $data['raw_email'],
      'parse_email' => $data['parse_email'] ?? null,
      'references' => $data['references'] ?? null,
    ]);

    return $mail;
  }
}