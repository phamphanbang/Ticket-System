@extends('mails.layout')


@section('title')
<h2>Your Ticket Has Been Confirmed and Is Being Worked On</h2>
@endsection

@section('content')
<p>Dear {{ $ticket->client->name }},</p>
<p>We're writing to confirm that your ticket <strong>#{{ $ticket->title }}</strong> has been received and is now being handled by <strong>{{ $ticket->assignTo->name }}</strong>.</p>

<p><strong>Details of your request:</strong></p>
<ul>
  <li><strong>Subject:</strong> {{ $ticket->description }}</li>
  <li><strong>Created on:</strong> {{ $ticket->created_at->format('d M Y H:i') }}</li>
  <li><strong>Last updated:</strong> {{ $ticket->updated_at->format('d M Y H:i') }}</li>
  <li><strong>Deadline:</strong> {{ $ticket->deadline->format('d M Y H:i') }}</li>
</ul>

<p>You can reply to this email if you have more information to share or questions about your ticket.</p>

<p>Thank you for your patience.<br>
  Best regards,<br>
  <strong>Support Team</strong>
</p>
@endsection