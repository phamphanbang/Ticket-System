@extends('mails.layout')

@section('title')
<h2>Your Ticket #{{ $ticket->id }} Needs Your Approval</h2>
@endsection

@section('content')
<p>Dear {{ $ticket->client->name }},</p>

<p>We have completed the analysis of your ticket and prepared an estimation for your review.</p>

<p><strong>Ticket Details:</strong></p>
<ul>
    <li><strong>Subject:</strong> {{ $ticket->subject }}</li>
    <li><strong>Description:</strong> {{ $ticket->description }}</li>
    <li><strong>Created on:</strong> {{ $ticket->created_at->format('d M Y H:i') }}</li>
    <li><strong>Last updated:</strong> {{ $ticket->updated_at->format('d M Y H:i') }}</li>
</ul>

<p>Please review the estimation and approve it to proceed with the work. You can view the full details and approve the ticket by clicking the button below:</p>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $url }}" style="background-color: #4CAF50; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px;">View and Approve Ticket</a>
</div>

<p>If you have any questions or concerns about the estimation, please don't hesitate to reply to this email.</p>

<p>Thank you for your cooperation.<br>
Best regards,<br>
<strong>Support Team</strong></p>
@endsection
