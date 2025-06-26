<p>Support Ticket Acknowledgment</p>

<p>Dear <strong>{{ $ticket->client->name }}</strong>,</p>

<p>Thank you for reaching out to <strong>{{ config('app.name') }} Support</strong>. This is to confirm that we have received your inquiry and a support ticket has been successfully created in our system.</p>

<p><strong>Ticket ID:</strong> {{ $ticket->id }}</p>

<p>Our support team is currently reviewing your request and will get back to you as soon as possible. If you have any additional information to share, please feel free to reply to this email. Your response will be automatically added to the ticket thread for our team to review.</p>

<p>We appreciate your patience and will ensure your issue is addressed promptly.</p>

<p>Best regards,</p>
<p><strong>The {{ config('app.name') }} Support Team</strong></p>