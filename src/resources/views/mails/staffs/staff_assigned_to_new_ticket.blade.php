@extends('mails.layout')


@section('title')
<h2>New Ticket Assigned to You</h2>
@endsection

@section('content')
<p>Hello {{ $ticket->assignTo->name }},</p>

<p>You have been assigned to a new support ticket: <strong>#{{ $ticket->id }} – "{{ $ticket->title }}"</strong>.</p>

<p><strong>Client Name:</strong> {{ $ticket->client_name }}</p>
<p><strong>Client Email:</strong> {{ $ticket->client_email }}</p>
<p><strong>Status:</strong> {{ $ticket->status->label() }}</p>
<p><strong>Subject:</strong> {{ $ticket->description }}</p>

<p><strong>Created At:</strong> {{ $ticket->created_at->format('d M Y H:i') }}</p>
<p><strong>Last Updated:</strong> {{ $ticket->updated_at->format('d M Y H:i') }}</p>
<p><strong>Deadline:</strong> {{ $ticket->deadline->format('d M Y H:i') }}</p>

<p>
  Please review the ticket details and take the appropriate actions.<br>
  Thank you,<br>
  <strong>Support Team</strong>
</p>
@endsection