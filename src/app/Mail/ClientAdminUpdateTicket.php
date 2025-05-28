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

class ClientAdminUpdateTicket extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Ticket $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                address: config('mail.from.address'),
                name: config('mail.from.name'),
            ),
            subject: 'Your Ticket#['.$this->ticket->id.'] has been updated',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.clients.admin_update_ticket',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
