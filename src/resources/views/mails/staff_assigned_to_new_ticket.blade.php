<h1>You have been assigned to a new ticket</h1>

<p><strong>Id:</strong> {{ $ticket->id }}</p>
<p><strong>Title:</strong> {{ $ticket->title }}</p>
<p><strong>Description:</strong> {{ $ticket->description }}</p>
<p><strong>Client name:</strong> {{ $ticket->client_name }}</p>
<p><strong>Client email:</strong> {{ $ticket->client_email }}</p>
<p><strong>Status:</strong> {{ $ticket->status->label()}}</p>
