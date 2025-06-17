<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\TicketEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Mime\Email;

class StaffResponse extends Mailable
{
  use Queueable, SerializesModels;

  public function __construct(
    public TicketEmail $ticketEmail,
    public array $email_attachments = [],
    public ?string $senderName = null,
  ) {
    $this->withSymfonyMessage(function (Email $message) {
      $message->getHeaders()->addTextHeader('In-Reply-To', $this->formatMessageId($this->ticketEmail->in_reply_to));

      $message->getHeaders()->addTextHeader('References', $this->formatMessageId($this->ticketEmail->in_reply_to));
    });
  }

  private function formatMessageId(string $id): string
  {
    return str_starts_with($id, '<') ? $id : "<{$id}>";
  }
  public function envelope(): Envelope
  {
    $fromName = $this->senderName ? $this->senderName : env('MAIL_FROM_NAME');
    return new Envelope(
      subject: $this->ticketEmail->subject,
      from: new Address(env('MAIL_FROM_ADDRESS'), $fromName),
      to: $this->ticketEmail->to_email,
    );
  }

  public function headers(): Headers
  {
    return new Headers(
      text: [
        'Ticket-Mail-Id' => $this->ticketEmail->id,
      ],
    );
  }


  public function content(): Content
  {
    return new Content(
      view: 'mails.staffs.staff-response',
      with: [
        'ticketEmail' => $this->ticketEmail,
      ]
    );
  }

  public function attachments(): array
  {
    $email_attachments = [];
    foreach ($this->email_attachments as $attachment) {
      $email_attachments[] = Attachment::fromStorage($attachment);
    }
    return $email_attachments;
  }
}
