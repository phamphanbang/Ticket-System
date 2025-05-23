@extends('mails.layout')


@section('title')
<h2>You Have Been Unassigned from a Ticket</h2>
@endsection

@section('content')
<p>Hello {{ $oldStaff->name }},</p>

<p>You have been unassigned from ticket <strong>#{{ $ticket->id }} – "{{ $ticket->title }}"</strong>.</p>

<p>If you believe this was a mistake or have any questions, please contact your supervisor or the support team.</p>

<p class="email-footer">
  Thank you,<br>
  Support Team
</p>
@endsection