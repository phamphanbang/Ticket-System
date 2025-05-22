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
        <h2>Your Ticket Has Been Delayed – We're On It</h2>
      </td>
    </tr>
    <tr>
      <td class="email-body">
        <p>Dear {{ $ticket->client_name }},</p>
        <p>We wanted to inform you that there has been a delay in processing your support ticket (Ticket #{{ $ticket->id }} – "{{ $ticket->title }}").</p>
        <p>The ticket has been delayed to <strong>{{ $ticket->deadline }}</strong></p>
        <p>We sincerely apologize for the delay and appreciate your patience. You can rest assured that your ticket is still being handled and we will keep you updated as progress is made.</p>
        <p>If you have any additional information to share, or questions in the meantime, please feel free to reply to this email.</p>

        <p>Thank you for your understanding.<br>Support Team</p>
      </td>
    </tr>
  </table>
</body>

</html>
