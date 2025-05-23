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

    .email-button {
      display: inline-block;
      padding: 10px 18px;
      margin-right: 10px;
      border-radius: 4px;
      font-size: 14px;
      text-decoration: none;
      color: #fff;
      font-weight: bold;
    }

    .reject-button {
      background-color: #e53e3e;
    }

    .close-button {
      background-color: #38a169;
    }
  </style>
</head>

<body>
  <table class="email-container" cellpadding="0" cellspacing="0">
    <tr>
      <td class="email-header">
        @include('title')
      </td>
    </tr>
    <tr>
      <td class="email-body">
        @include('content')
      </td>
    </tr>
  </table>
</body>

</html>