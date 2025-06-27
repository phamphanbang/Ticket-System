<?php

namespace App\Services;

use App\Events\MailCreated;
use App\Mail\StaffResponse;
use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\TicketEmail;
use App\Validators\TicketValidator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class MailService
{
  public function __construct(
    protected AttachmentService $attachmentService,
  ) {}
  public function index($ticketId, $limit, $cursor)
  {
    $ticket = Ticket::where('id', $ticketId)->first();
    TicketValidator::checkTicketExists($ticket);
    $query = $ticket->ticketEmails()->with('attachments')->orderBy('created_at', 'desc');
    if($cursor) {
      $cursorMail = TicketEmail::where('id', $cursor)->first();
      $mails = $query->where('created_at', '<', $cursorMail->created_at);
    }
    $mails = $query->take($limit + 1)->get();
    return $mails->reverse();
  }

  public function send($ticketId, $data)
  {
    $ticket = Ticket::where('id', $ticketId)->first();
    TicketValidator::checkTicketExists($ticket);
    $user = Auth::user();
    $body = $data['content'] ?? $data['body'];

    $latestMail = $ticket->ticketEmails()->where('message_id', '!=', null)->oldest()->first();
    $customMessageId = $this->generateMessageId();
    $mail = TicketEmail::create([
      'message_id' => $customMessageId,
      'in_reply_to' => $latestMail->message_id,
      'from_email' => env('MAIL_FROM_ADDRESS'),
      'from_name' => $user->name,
      'to_email' => $ticket->client->email,
      'body' => $body,
      'subject' => 'Re: ' . $latestMail->subject,
      'type' => 'reply',
      'ticket_id' => $ticket->id,
      'received_at' => now(),
    ]);

    $attachments = [];
    if (isset($data['attachments'])) {
      foreach ($data['attachments'] as $attachment) {
        $attachments[] = $this->attachmentService->saveAttachment($attachment, null, $ticket->id, $mail->id);
      }
    }

    if (isset($data['attachment_ids']) && !empty($data['attachment_ids'])) {
      foreach ($data['attachment_ids'] as $attachmentId) {
        $attachment = Attachment::findOrFail($attachmentId);
        $attachment->update(['file_status' => 'used']);
      }
    }

    $mailable = new StaffResponse($mail, $attachments);
    Mail::to($ticket->client->email)->queue($mailable);
    event(new MailCreated($mail));
    return $mail;
  }

  function generateMessageId(string $domain = null): string
  {
    $domain = $domain ?? env('MAIL_FROM_DOMAIN', 'myapp.com');
    $unique = bin2hex(random_bytes(16));
    return sprintf(
        '%s.%d@%s',
        $unique,
        time(),
        $domain,
    );
  } 
}
