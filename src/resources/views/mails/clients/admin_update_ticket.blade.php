@extends('mails.layout')


@section('title')
<h2>Update on Your Support Ticket</h2>
@endsection

@section('content')
<p>Dear {{ $ticket->client->name }},</p>

<p>We wanted to let you know that your ticket <strong>#{{ $ticket->title }}</strong> has been updated.</p>

@if ($ticket->assign_to)
<p>It has now been assigned to <strong>{{ $ticket->assignTo->name }}</strong>, who will assist you further.</p>
@else
<p>At this time, your ticket has not yet been assigned to a support representative. We’ll assign it shortly.</p>
@endif

<p><strong>Details of your request:</strong></p>
<ul>
  <li><strong>Subject:</strong> {{ $ticket->description }}</li>
  <li><strong>Created on:</strong> {{ $ticket->created_at->format('d M Y H:i') }}</li>
  <li><strong>Last updated:</strong> {{ $ticket->updated_at->format('d M Y H:i') }}</li>
  <li><strong>Deadline:</strong> {{ $ticket->deadline->format('d M Y H:i') }}</li>
</ul>

<p>You can reply to this email if you have more information to share or questions about your ticket.</p>

<p>Thank you for reaching out.<br>
  Best regards,<br>
  <strong>Support Team</strong>
</p>
@endsection