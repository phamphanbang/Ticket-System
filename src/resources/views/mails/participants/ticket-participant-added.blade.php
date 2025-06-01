@extends('mails.layout')

@section('title')
<h2>You've Been Added to a Ticket</h2>
@endsection

@section('content')
<p>Hello {{ $user->name }},</p>

<p>You have been added to ticket <strong>#{{ $ticket->id }} – "{{ $ticket->title }}"</strong> as a {{ $participant->role_in_ticket }}.</p>

<p>You were invited by {{ $invitedBy->name }}.</p>

<p>You can view the ticket by clicking <a href="{{ config('app.frontend_url') . '/tickets/' . $ticket->id }}">here</a>.</p>

<p>
  Thank you,<br>
  <strong>{{ config('app.name') }}</strong>
</p>
@endsection