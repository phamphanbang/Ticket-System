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

    $data['body'] = $this->removeGmailQuoteBlock($message->getHTMLBody());
    return $data;
  }

  function removeGmailQuoteBlock(string $html): string
  {
    libxml_use_internal_errors(true); // suppress malformed HTML warnings

    $dom = new \DOMDocument();
    $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

    $xpath = new \DOMXPath($dom);

    // Match divs with both `gmail_quote` and `gmail_quote_container` classes
    $nodes = $xpath->query('//div[contains(@class, "gmail_quote") and contains(@class, "gmail_quote_container")]');

    foreach ($nodes as $node) {
      $node->parentNode->removeChild($node);
    }

    // Return cleaned HTML without <body> tag
    $body = $dom->saveHTML($dom->getElementsByTagName('body')->item(0));
    return preg_replace('/^<body>|<\/body>$/', '', $body);
  }

  public static function generateMessageId(string $domain = null): string
  {
    // Use a safe default domain if none provided
    $domain = $domain ?? env('MAIL_FROM_DOMAIN', 'myapp.com');
    
    // Generate a unique identifier that's safe for message IDs
    $unique = bin2hex(random_bytes(16)); // 32 hex characters
    
    // Format according to RFC 2822
    return sprintf(
        '%s.%d@%s',
        $unique,
        time(),
        $domain
    );
  }
}
