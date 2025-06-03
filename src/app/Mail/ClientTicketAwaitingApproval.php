<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ClientTicketAwaitingApproval extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Ticket $ticket
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Ticket #{$this->ticket->id} Needs Your Approval",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mails.clients.awaiting-approval',
            with: [
                'ticket' => $this->ticket,
                'url' => config('app.url') . "/tickets/{$this->ticket->id}"
            ]
        );
    }
}
