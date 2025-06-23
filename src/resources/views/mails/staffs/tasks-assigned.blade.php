@extends('mails.layout')

@section('title')
<h2>New Task Assignment</h2>
@endsection

@section('content')
<p>Dear {{ $user->name }},</p>

<p>You have been assigned a new task: <strong>{{ $task->title }}</strong></p>

<p><strong>Task Details:</strong></p>
<ul>
    <li><strong>Title:</strong> {{ $task->title }}</li>
    <li><strong>Description:</strong> {{ $task->description }}</li>
    <li><strong>Phase:</strong> {{ ucfirst($task->phase) }}</li>
    <li><strong>Status:</strong> {{ $task->phase === 'estimate' ? \App\Constants\EstimationStatus::from($task->estimation_status)->label() : \App\Constants\ExecutionStatus::from($task->execution_status)->label() }}</li>
    @if($task->estimated_time)
    <li><strong>Estimated Time:</strong> {{ $task->estimated_time }} hours</li>
    @endif
    <li><strong>Assigned Date:</strong> {{ $task->updated_at->format('d M Y H:i') }}</li>
</ul>

<p>Please review the task details and begin working on it according to the specified requirements.</p>

<p>If you have any questions or need clarification, please don't hesitate to reach out to your supervisor.</p>

<p>Best regards,<br>
<strong>Task Management System</strong></p>
@endsection
