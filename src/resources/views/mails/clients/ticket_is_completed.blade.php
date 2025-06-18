@extends('mails.layout')

@section('title')
<h2>Your Support Ticket Has Been Completed</h2>
@endsection

@section('content')
<p>Dear {{ $ticket->client->name }},</p>

<p>We’d like to inform you that your support ticket <strong>#{{ $ticket->title }}</strong> has been successfully closed.</p>

<p>If you have any questions, feedback, or if something still needs attention, please  open a new support ticket.</p>

<p>Thank you for reaching out to us.<br>
Best regards,<br>
<strong>Support Team</strong></p>
@endsection