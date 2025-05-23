@extends('mails.layout')


@section('title')
<h2>Your Ticket Has Been Rejected</h2>
@endsection

@section('content')
<p>Hello {{ $ticket->assignTo->name }},</p>

<p>The client has rejected the resolution of ticket <strong>#{{ $ticket->id }} – "{{ $ticket->title }}"</strong>.</p>

<p>Please review the ticket and take the necessary follow-up actions. You may need to communicate further with the client to resolve the issue to their satisfaction.</p>

<p>If you have any questions or need assistance, feel free to reach out to the support manager.</p>

<p>
  Thank you,<br>
  <strong>Support Team</strong>
</p>
@endsection