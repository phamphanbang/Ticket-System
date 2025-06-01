<?php

namespace App\Mail;

use App\Models\TicketParticipant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class TicketParticipantRemoved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TicketParticipant $participant,
        public string $removedByUserId
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You've been removed from a ticket"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.participants.ticket-participant-removed',
            with: [
                'participant' => $this->participant,
                'ticket' => $this->participant->ticket,
                'user' => $this->participant->user,
                'removedBy' => User::find($this->removedByUserId),
            ]
        );
    }
} 