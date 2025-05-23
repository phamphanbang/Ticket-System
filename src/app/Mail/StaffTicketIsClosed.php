<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffTicketIsClosed extends Mailable implements ShouldQueue
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
            subject: 'A ticket assigned to you has been closed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.staffs.ticket_is_closed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
