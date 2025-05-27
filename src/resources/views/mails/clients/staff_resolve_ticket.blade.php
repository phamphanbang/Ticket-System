@extends('mails.layout')

@section('title')
<h2>Your Ticket Has Been Resolved</h2>
@endsection

@section('content')

<p>Hello {{ $ticket->client->name }},</p>

<p>Your ticket <strong>#{{ $ticket->title }}</strong> has been resolved by <strong>{{ $ticket->assignTo->name }}</strong>.</p>

<p><strong>Subject:</strong> {{ $ticket->description }}</p>

<p>Please confirm the resolution of this ticket using the buttons below. If no action is taken, the ticket will automatically close in 3 days.</p>

<p>
  <a href="{{ $rejectUrl }}" class="email-button reject-button" target="_blank">Reject</a>
  <a href="{{ $closeUrl }}" class="email-button close-button" target="_blank">Confirm & Close</a>
</p>

<p><strong>Created on:</strong> {{ $ticket->created_at->format('d M Y H:i') }}</p>
<p><strong>Last updated:</strong> {{ $ticket->updated_at->format('d M Y H:i') }}</p>
<p><strong>Deadline:</strong> {{ $ticket->deadline->format('d M Y H:i') }}</p>

<p>Thank you for your patience.<br>
  Best regards,<br>
  <strong>Support Team</strong>
</p>
@endsection