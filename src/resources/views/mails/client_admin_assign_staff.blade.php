<h1>Your ticket has been assigned to a staff member</h1>

<p><strong>Title:</strong> {{ $ticket->title }}</p>
<p><strong>Description:</strong> {{ $ticket->description }}</p>
<p><strong>Status:</strong> {{ $ticket->status->label()}}</p>
<p><strong>Assign to:</strong> {{ $ticket->assign_to ? $ticket->assignTo->name : 'none'}}</p>
<p><strong>Updated at:</strong> {{ $ticket->updated_at}}</p>
