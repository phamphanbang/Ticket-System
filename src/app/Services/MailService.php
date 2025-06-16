<?php 

namespace App\Services;

use App\Mail\StaffResponse;
use App\Models\Ticket;
use App\Models\TicketEmail;
use App\Validators\TicketValidator;
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

    $latestMail = $ticket->ticketEmails()->where('message_id', '!=', null)->latest()->first();
    $mail = TicketEmail::create([
      'in_reply_to' => $latestMail->message_id,
      'from_email' => env('MAIL_FROM_ADDRESS'),
      'to_email' =>$ticket->client->email,
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
    $messageId = null;
    // Mail::to($ticket->client->email)->queue(new StaffResponse($mail, $attachments));
    Mail::send(new StaffResponse($mail, $attachments),[], function ($message) use ($ticket, &$messageId) {
      $messageId = $message->getMessageId();
    });
    $mail->update([
      'message_id' => $messageId,
    ]);
    return $mail;
  }
}