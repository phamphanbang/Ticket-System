<?php

namespace App\Helpers;

use Webklex\PHPIMAP\Message;

class MailHelper
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
    $data['to_email'] = $message->getTo()[0]->mail;
    $data['subject'] = $message->getSubject();
    $data['htmlBody'] = $message->getHTMLBody();

    $body = $message->getTextBody() ?: strip_tags($data['htmlBody']);
    $data['body'] = $this->extractReplyFromEmail($body);
    $data['parse_email'] = $message->getTextBody() ?: strip_tags($data['htmlBody']);
    return $data;
  }

  function extractReplyFromEmail($body)
  {
    $pattern = '/^On .+ wrote:|^From:|^Vào .*? viết:/mi';

    $parts = preg_split($pattern, $body, 2);

    return trim($parts[0]);
  }
}
