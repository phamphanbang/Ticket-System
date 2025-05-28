@extends('mails.layout')

@section('title')
<h2>New Comment on Your Support Ticket</h2>
@endsection

@section('content')
<p>Hello {{ $client->name }},</p>

<p>A new comment has been added to your support ticket <strong>#{{ $ticket->id }} – "{{ $ticket->title }}"</strong> by our support team.</p>

<p><strong>Comment:</strong></p>
<blockquote style="border-left: 4px solid #ccc; padding-left: 1em; color: #555;">
  {{ $comment->body }}
</blockquote>

<p>You can reply to this email to continue the conversation, or visit your ticket portal for more details.</p>

<p class="email-footer">
  Thank you,<br>
  Support Team
</p>
@endsection