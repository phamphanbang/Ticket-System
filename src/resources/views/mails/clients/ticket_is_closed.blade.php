@extends('mails.layout')

@section('title')
<h2>Your Support Ticket Has Been Completed</h2>
@endsection

@section('content')
<p>Dear {{ $ticket->client->name }},</p>

<p>We’d like to inform you that your support ticket <strong>#{{ $ticket->subject }}</strong> has been successfully closed.</p>

<p><strong>Summary of your request:</strong></p>
<div>{{ $ticket->description }}</div>

<p><strong>Tasks completed:</strong></p>
<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
    <thead>
        <tr style="background-color: #f3f4f6;">
            <th style="padding: 12px; border: 1px solid #e5e7eb; text-align: left;">Task title</th>
            <th style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">Task Description</th>
            <th style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">Estimated Time (hour)</th>
            <th style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">Actual Time (hour)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tasks as $task)
        <tr>
            <td style="padding: 12px; border: 1px solid #e5e7eb;">{{ $task->title }}</td>
            <td style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">{{ $task->description }}</td>
            <td style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">{{ $task->estimated_time }}</td>
            <td style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">{{ $task->actual_time }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<p>If you have any questions, feedback, or if something still needs attention, please don’t hesitate to reply to this email or open a new support ticket.</p>

<p>Thank you for reaching out to us.<br>
Best regards,<br>
<strong>Support Team</strong></p>
@endsection