@extends('mails.layout')


@section('title')
<h2>Your Support Ticket Has Been Created</h2>
@endsection

@section('content')
<p>Dear {{ $ticket->client_name }},</p>

<p>We’ve received your request and created a new support ticket: <strong>#{{ $ticket->title }}</strong>.</p>

@if ($ticket->assign_to)
<p>We’ve assigned your ticket to <strong>{{ $ticket->assignTo->name }}</strong>, who will be assisting you shortly.</p>
@else
<p>Your ticket is currently awaiting assignment. A support agent will be assigned soon.</p>
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
<strong>Support Team</strong></p>
@endsection