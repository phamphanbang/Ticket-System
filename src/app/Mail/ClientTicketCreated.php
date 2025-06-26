<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\TicketEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class ClientTicketCreated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Ticket $ticket;
    public TicketEmail $ticketEmail;

    public function __construct(Ticket $ticket, TicketEmail $ticketEmail)
    {
        $this->ticket = $ticket;
        $this->ticketEmail = $ticketEmail;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                address: config('mail.from.address'),
                name: config('mail.from.name'),
            ),
            subject: '[ESReport] '. $this->ticket->title,
        );
    }

    public function headers(): Headers
    {
      return new Headers(
        messageId: $this->ticketEmail->message_id,
        text: [
          'Ticket-Mail-Id' => $this->ticketEmail->id,
        ],
      );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.clients.admin_create_new_ticket',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
