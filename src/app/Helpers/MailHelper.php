<?php

namespace App\Helpers;

use Carbon\Carbon;
use Webklex\PHPIMAP\Message;

class MailHelper
{
  public function processIMAPEmail(Message $message)
  {
    $from = $message->getFrom()[0];
    $data['message_id'] = $message->getMessageId()->get();
    $data['in_reply_to'] = $message->getInReplyTo()->get();
    $data['references'] = $message->getReferences()->toArray() ?? [];
    $data['raw_email'] = $message->getRawMessage()->get();
    $data['from_email'] = $from->mail;
    $data['from_name'] = $from->personal ?: 'Unknown Client';
    $data['to_email'] = $message->getTo()[0]->mail;
    $data['subject'] = $message->getSubject()->get();
    $data['htmlBody'] = $message->getHTMLBody();
    $data['created_at'] = Carbon::parse($message->getDate())->toDateTimeString();
    $data['attachments'] = $message->getAttachments();

    // $body = $message->getTextBody() ?: strip_tags($data['htmlBody']);
    // $data['body'] = $this->extractReplyFromEmail($body);
    // $data['parse_email'] = $message->getTextBody() ?: strip_tags($data['htmlBody']);
    $data['body'] = $this->extractReplyFromEmail($message->getTextBody());
    // $data['bodies'] = $message->getBodies();
    return $data;
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
