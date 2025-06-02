@extends('mails.layout')

@section('title')
<h2>Task Unassignment Notification</h2>
@endsection

@section('content')
<p>Dear {{ $task->assignedUser->name }},</p>

<p>You have been unassigned from the task: <strong>{{ $task->title }}</strong></p>

<p><strong>Task Details:</strong></p>
<ul>
    <li><strong>Title:</strong> {{ $task->title }}</li>
    <li><strong>Description:</strong> {{ $task->description }}</li>
    <li><strong>Phase:</strong> {{ ucfirst($task->phase) }}</li>
    <li><strong>Status:</strong> {{ $task->phase === 'estimate' ? \App\Constants\EstimationStatus::from($task->estimation_status)->label() : \App\Constants\ExecutionStatus::from($task->execution_status)->label() }}</li>
    @if($task->estimated_time)
    <li><strong>Estimated Time:</strong> {{ $task->estimated_time }} hours</li>
    @endif
    <li><strong>Unassigned Date:</strong> {{ $task->updated_at->format('d M Y H:i') }}</li>
</ul>

<p>You are no longer responsible for this task. If you have any questions about this change, please contact your supervisor.</p>

<p>Best regards,<br>
<strong>Task Management System</strong></p>
@endsection
