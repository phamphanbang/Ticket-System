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
        <h2>You have been unassigned to a ticket</h2>
      </td>
    </tr>
    <tr>
      <td class="email-body">
        <p>Hello {{ $ticket->assignTo->name }},</p>
        <p>You have been unassigned to ticket <strong>#{{ $ticket->title }}</strong></p>


        <p class="email-footer">Thank you,<br>Support Team</p>
      </td>
    </tr>
  </table>
</body>

</html>
