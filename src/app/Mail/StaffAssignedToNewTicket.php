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

class StaffAssignedToNewTicket extends Mailable implements ShouldQueue
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
            subject: 'You have been assigned a new ticket',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.staffs.staff_assigned_to_new_ticket',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
