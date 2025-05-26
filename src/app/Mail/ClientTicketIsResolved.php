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
use Illuminate\Support\Facades\URL;

class ClientTicketIsResolved extends Mailable implements ShouldQueue
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
            subject: 'Your ticket has been resolved',
        );
    }

    public function content(): Content
    {
        $rejectUrl = $this->parseURL($this->ticket,'client.reject.ticket','/reject-ticket');
        $closeUrl = $this->parseURL($this->ticket,'client.close.ticket','/close-ticket');
        return new Content(
            view: 'mails.clients.staff_resolve_ticket',
            with: [
                'ticket' => $this->ticket,
                'rejectUrl' => $rejectUrl,
                'closeUrl' => $closeUrl,
            ],
        );
    }

    public function parseURL($ticket,$urlName,$link)
    {
        $signedURL = URL::temporarySignedRoute(
            name: $urlName,
            expiration: now()->addDays(3),
            parameters: [
                'ticket' => $this->ticket->id,
            ]
        );
        $parsedUrl = parse_url($signedURL);
        parse_str($parsedUrl['query'], $queryParams);

        $url = env('SESSION_DOMAIN') . $link . '?' . http_build_query([
            'id' => $this->ticket->id,
            'expires' => $queryParams['expires'],
            'signature' => $queryParams['signature'],
        ]);

        return $url;
    }

    public function attachments(): array
    {
        return [];
    }
}
