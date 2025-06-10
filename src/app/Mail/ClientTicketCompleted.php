<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientTicketCompleted extends Mailable
{
  use Queueable, SerializesModels;

  public function __construct(
    public Ticket $ticket
  ) {}

  public function envelope(): Envelope
  {
    return new Envelope(
      subject: "Ticket Closure Notification"
    );
  }

  public function content(): Content
  {
    return new Content(
      view: 'mails.clients.ticket_is_completed',
      with: [
        'ticket' => $this->ticket,
        'client' => $this->ticket->client,
      ]
    );
  }
}
