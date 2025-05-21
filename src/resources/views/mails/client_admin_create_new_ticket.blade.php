<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
</head>

<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
  <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 6px; overflow: hidden;">
    <tr>
      <td style="background-color: #2d3748; color: white; padding: 16px; text-align: center;">
        <h2 style="margin: 0;">Your ticket has been created</h2>
      </td>
    </tr>
    <tr>
      <td style="padding: 24px;">
        <p style="font-size: 16px;">Hello {{ $ticket->client_name }},</p>
        <p style="font-size: 16px;">A new ticket <strong>#{{ $ticket->title }}</strong> has been created by your request.</p>

        @if ($ticket->assign_to)
        <p style="font-size: 16px;">Your ticket has been assigned to: {{ $ticket->assignTo->name }}</p>
        @else
        <p style="font-size: 16px;">Your ticket has not been assigned to anyone yet.</p>
        @endif

        <p style="font-size: 14px; color: #555;">Subject: {{ $ticket->description }}</p>

        <!-- <a href="{{ url('/tickets/' . $ticket->id) }}"
                   style="display: inline-block; margin-top: 20px; background-color: #3182ce; color: white; padding: 12px 20px; text-decoration: none; border-radius: 4px;">
                    View Ticket
                </a> -->

        <p style="font-size: 16px;">Ticket is created at: {{ $ticket->created_at->format('d M Y H:i') }}.</p>
        <p style="font-size: 16px;">Ticket is updated at: {{ $ticket->updated_at->format('d M Y H:i') }}.</p>
        <p style="font-size: 16px;">Ticket deadline: {{ $ticket->deadline->format('d M Y H:i') }}</p>

        <p style="margin-top: 30px; font-size: 14px; color: #777;">Thank you,<br>Support Team</p>
      </td>
    </tr>
  </table>
</body>

</html>