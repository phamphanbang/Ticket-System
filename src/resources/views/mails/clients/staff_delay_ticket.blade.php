@extends('mails.layout')


@section('title')
<h2>Your Ticket Has Been Delayed – We're On It</h2>
@endsection

@section('content')
<p>Dear {{ $ticket->client->name }},</p>

<p>We wanted to inform you that there has been a delay in processing your support ticket <strong>#{{ $ticket->id }} – "{{ $ticket->title }}"</strong>.</p>

<p>The new estimated resolution deadline is <strong>{{ $ticket->deadline->format('d M Y H:i') }}</strong>.</p>

<p>We sincerely apologize for this delay and truly appreciate your patience. Please be assured that your ticket is still actively being worked on, and we’ll continue to keep you updated on its progress.</p>

<p>You can reply to this email if you have more information to share or questions about your ticket.</p>

<p>Thank you for your patience.<br>
  Best regards,<br>
  <strong>Support Team</strong>
</p>
@endsection