@extends('mails.layout')

@section('title')
<h2>You've Been Removed from a Ticket</h2>
@endsection

@section('content')
<p>Hello {{ $user->name }},</p>

<p>You have been removed from ticket <strong>#{{ $ticket->id }} – "{{ $ticket->title }}"</strong>.</p>

<p>You were removed by {{ $removedBy->name }}.</p>

<p>If you believe this was done in error, please contact your team leader.</p>

<p>
  Thank you,<br>
  <strong>{{ config('app.name') }}</strong>
</p>
@endsection