@extends('mails.layout')

@section('title')
<h2>Support Ticket Confirmation</h2>
@endsection

@section('content')
<h1>Hello {{ $ticket->client->name }},</h1>

<p>Thank you for contacting {{ config('app.name') }}. We’ve received your request and created a support ticket.</p>

<p>This email is to confirm that your ticket has been successfully submitted to our system. A member of our team will review it shortly.</p>

<div class="ticket-details">
    <p><strong>Ticket ID:</strong> {{ $ticket->id }}</p>
    <p><strong>Subject:</strong> {{ $ticket->subject }}</p>
    <p><strong>Created At:</strong> {{ $ticket->created_at->format('F j, Y, g:i a') }}</p>
    @if(!empty($ticket->description))
    <p><strong>Description:</strong><br>{{ $ticket->description }}</p>
    @endif
</div>

<p>If you have any additional information to provide, simply reply to this email and your response will be added to the ticket thread automatically.</p>
<p>Thank you again for reaching out to us.<br>The {{ config('app.name') }} Support Team</p>
@endsection