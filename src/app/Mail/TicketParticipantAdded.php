<?php

namespace App\Mail;

use App\Models\TicketParticipant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketParticipantAdded extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TicketParticipant $participant
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You've been added to a ticket"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-participant-added',
            with: [
                'participant' => $this->participant,
                'ticket' => $this->participant->ticket,
                'user' => $this->participant->user,
                'invitedBy' => $this->participant->invitedBy,
            ]
        );
    }
} 