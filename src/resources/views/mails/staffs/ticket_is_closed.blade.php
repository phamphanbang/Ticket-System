@extends('mails.layout')


@section('title')
<h2>A Ticket Assigned to You Has Been Closed</h2>
@endsection

@section('content')
<p>Dear {{ $ticket->assignTo->name }},</p>
<p>We would like to inform you that ticket <strong>#{{ $ticket->id }} – "{{ $ticket->title }}"</strong> has been closed.</p>

<p>Thank you for your support and understanding.<br>Support Team</p>
@endsection