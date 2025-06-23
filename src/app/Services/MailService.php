<?php

namespace App\Services;

use App\Jobs\FetchInfoCommandJob;
use App\Events\MailCreated;
use App\Mail\StaffResponse;
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
  public function index($ticketId)
  {
    $ticket = Ticket::where('id', $ticketId)->first();
    TicketValidator::checkTicketExists($ticket);
    $mails = $ticket->ticketEmails()->with('attachments')->get();
    return $mails;
  }

  public function send($ticketId, $data)
  {
    $ticket = Ticket::where('id', $ticketId)->first();
    TicketValidator::checkTicketExists($ticket);
    $user = Auth::user();

    $latestMail = $ticket->ticketEmails()->where('message_id', '!=', null)->oldest()->first();
    $customMessageId = $this->generateMessageId();
    $mail = TicketEmail::create([
      'message_id' => $customMessageId,
      'in_reply_to' => $latestMail->message_id,
      'from_email' => env('MAIL_FROM_ADDRESS'),
      'from_name' => $user->name,
      'to_email' => $ticket->client->email,
      'body' => $data['body'],
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

    $mailable = new StaffResponse($mail, $attachments);
    Mail::to($ticket->client->email)->queue($mailable);
    event(new MailCreated($mail));
    return $mail;
  }

  function generateMessageId(string $domain = null): string
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
