<?php

namespace App\Services;

use App\Models\TicketMail;
use Illuminate\Support\Facades\Log;
use Webklex\PHPIMAP\Message;

class TicketMailService
{
  public function processIMAPEmail(Message $message)
  {
    $from = $message->getFrom()[0];
    $data['message_id'] = $message->getMessageId();
    $data['in_reply_to'] = $message->getInReplyTo()->get();
    $data['references'] = $message->getReferences()->get() ?? [];
    $data['raw_email'] = $message->getRawMessage();
    $data['from_email'] = $from->mail;
    $data['from_name'] = $from->personal ?: 'Unknown Client';
    $data['subject'] = $message->getSubject();
    $data['htmlBody'] = $message->getHTMLBody();

    $body = $message->getTextBody() ?: strip_tags($data['htmlBody']);
    $data['body'] = $this->extractReplyFromEmail($body);
    $data['parse_email'] = $message->getTextBody() ?: strip_tags($data['htmlBody']);
    return $data;
  }
  public function createTicketMail($data)
  {
    $mail = TicketMail::create($data);

    return $mail;
  }

  public function findByMessageId($messageId)
  {
    return TicketMail::where('message_id', $messageId)->first();
  }

  function extractReplyFromEmail($body) {
    $pattern = '/^On .+ wrote:|^From:|^Vào .*? viết:/mi';

    $parts = preg_split($pattern, $body, 2);

    return trim($parts[0]);
}
}
