<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketClosed extends Mailable
{
  use Queueable, SerializesModels;

  public function __construct(
    public Ticket $ticket
  ) {}

  public function envelope(): Envelope
  {
    return new Envelope(
      subject: "Ticket Closure Notification"
    );
  }

  public function content(): Content
  {
    $totalRealTime = 0;
    $tasks = $this->ticket->tasks->map(function ($task) use (&$totalRealTime) {
      $realTime = null;
      $startTimeLog = $task->auditLogs()
        ->where('description', 'Task execution started')
        ->orderBy('created_at')
        ->first();

      $endTimeLog = $task->auditLogs()
        ->where('description', 'like', '%Task execution marked as complete by leader%')
        ->orderBy('created_at')
        ->first();

      $realTime = $endTimeLog->created_at->diffInMinutes($startTimeLog->created_at);
      $totalRealTime += $realTime;
      return [
        'name' => $task->name,
        'estimated_time' => $task->estimated_time,
        'real_time' => $realTime
      ];
    });

    return new Content(
      view: 'mails.clients.ticket_is_closed',
      with: [
        'ticket' => $this->ticket,
        'client' => $this->ticket->client,
        'tasks' => $tasks
      ]
    );
  }
}
