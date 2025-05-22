<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      padding: 20px;
    }

    .email-container {
      max-width: 600px;
      margin: auto;
      background-color: #ffffff;
      border-radius: 6px;
      overflow: hidden;
      width: 100%;
    }

    .email-header {
      background-color: #2d3748;
      color: white;
      padding: 16px;
      text-align: center;
    }

    .email-header h2 {
      margin: 0;
    }

    .email-body {
      padding: 24px;
    }

    .email-body p {
      font-size: 16px;
    }

    .email-body .description {
      font-size: 14px;
      color: #555;
    }

    .email-footer {
      margin-top: 30px;
      font-size: 14px;
      color: #777;
    }
  </style>
</head>

<body>
  <table class="email-container" cellpadding="0" cellspacing="0">
    <tr>
      <td class="email-header">
        <h2>Your ticket has been confirmed and being worked on.</h2>
      </td>
    </tr>
    <tr>
      <td class="email-body">
        <p>Hello {{ $ticket->client_name }},</p>
        <p>Your ticket <strong>#{{ $ticket->title }}</strong> has been confirmed and being worked on by {{ $ticket->assignTo->name }}</p>

        <p class="description">Subject: {{ $ticket->description }}</p>

        <p>Ticket is created at: {{ $ticket->created_at->format('d M Y H:i') }}.</p>
        <p>Ticket is updated at: {{ $ticket->updated_at->format('d M Y H:i') }}.</p>
        <p>Ticket deadline: {{ $ticket->deadline->format('d M Y H:i') }}</p>

        <p class="email-footer">Thank you,<br>Support Team</p>
      </td>
    </tr>
  </table>
</body>

</html>
