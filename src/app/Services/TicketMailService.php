<?php

namespace App\Services;

use App\Models\TicketMail;
use Webklex\PHPIMAP\Message;

class TicketMailService
{
  public function processIMAPEmail(Message $message)
  {
    $from = $message->getFrom()[0];
    $data['message_id'] = $message->getMessageId();
    $data['in_reply_to'] = $message->getInReplyTo();
    $data['references'] = $message->getReferences() ?? [];
    $data['raw_email'] = $message->getRawContent();
    $data['from_email'] = $from->mail;
    $data['from_name'] = $from->personal ?: 'Unknown Client';
    $data['subject'] = $message->getSubject();
    $data['htmlBody'] = $message->getHTMLBody();
    $data['body'] = $message->getTextBody() ?: strip_tags($data['htmlBody']);
    $data['parse_email'] = $message->getTextBody() ?: strip_tags($data['htmlBody']);
    return $data;
  }
  public function createTicketMail($data)
  {
    $mail = TicketMail::create($data);

    return $mail;
  }
}
