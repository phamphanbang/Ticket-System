@extends('mails.layout')

@section('title')
<h2>Your Support Ticket Is Being Processed</h2>
@endsection

@section('content')
<p>Dear {{ $ticket->client->name }},</p>

<p>We're now processing your support ticket: <strong>#{{ $ticket->subject }}</strong>.</p>

<p><strong>Current status of your request:</strong></p>
<ul>
    <li><strong>Subject:</strong> {{ $ticket->description }}</li>
    <li><strong>Status:</strong> In Processing</li>
    <li><strong>Last updated:</strong> {{ $ticket->updated_at->format('d M Y H:i') }}</li>
</ul>

<p>Our team is actively working on your request. We'll keep you updated on any progress.</p>

<p>You can reply to this email if you have additional information to share.</p>

<p>Thank you for your patience.<br>
Best regards,<br>
<strong>Support Team</strong></p>
@endsection
