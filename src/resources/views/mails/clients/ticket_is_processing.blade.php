@extends('mails.layout')

@section('title')
<h2>Your Support Ticket Is Being Processed</h2>
@endsection

@section('content')
<p>Dear {{ $ticket->client->name }},</p>

<p>We're now processing your support ticket: <strong>#{{ $ticket->subject }}</strong>.</p>

<p><strong>Tasks planned to be completed:</strong></p>
<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
    <thead>
        <tr style="background-color: #f3f4f6;">
            <th style="padding: 12px; border: 1px solid #e5e7eb; text-align: left;">Task title</th>
            <th style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">Task Description</th>
            <th style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">Estimated Time (hour)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tasks as $task)
        <tr>
            <td style="padding: 12px; border: 1px solid #e5e7eb;">{{ $task->title }}</td>
            <td style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">{{ $task->description }}</td>
            <td style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">{{ $task->estimated_time }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<p>Our team is actively working on your request. We'll keep you updated on any progress.</p>

<p>You can reply to this email if you have additional information to share.</p>

<p>Thank you for your patience.<br>
Best regards,<br>
<strong>Support Team</strong></p>
@endsection
