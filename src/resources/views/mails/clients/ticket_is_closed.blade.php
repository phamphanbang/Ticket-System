@extends('mails.layout')


@section('title')
<h2>Your Ticket Has Been Closed</h2>
@endsection

@section('content')
<p>Dear {{ $ticket->client_name }},</p>

<p>We would like to inform you that your support ticket <strong>#{{ $ticket->id }} – "{{ $ticket->title }}"</strong> has been successfully closed.</p>

<p>Thank you for your patience.<br>
  Best regards,<br>
  <strong>Support Team</strong>
</p>
@endsection