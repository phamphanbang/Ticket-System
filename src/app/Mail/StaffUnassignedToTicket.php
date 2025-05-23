<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffUnassignedToTicket extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Ticket $ticket;
    public User $oldStaff;

    /**
     * Create a new message instance.
     */
    public function __construct(Ticket $ticket,User $oldStaff)
    {
        $this->ticket = $ticket;
        $this->oldStaff = $oldStaff;
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
            subject: 'You have been unassigned from ticket',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.staffs.staff_unassigned_to_ticket',
            with: [
                'ticket' => $this->ticket,
                'staff' => $this->oldStaff,
            ],
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
