<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientTicketIsConfirmed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Ticket $ticket;
    /**
     * Create a new message instance.
     */
    public function __construct(Ticket $ticket)
    {   
        $this->ticket = $ticket;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                address: config('mail.from.address'),
                name: config('mail.from.name'),
            ),
            subject: 'Your ticket has been confirmed and being worked on',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.clients.staff_confirm_ticket',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
