<?php

namespace App\Services;

use Google\Client;
use Google\Service\Gmail;

class GmailService
{
  protected $client;
  protected $gmailService;

  public function __construct()
  {
    $this->client = new Client();
    $this->client->setApplicationName('Laravel Gmail API');
    $this->client->setScopes([Gmail::GMAIL_READONLY]);
    $this->client->setAuthConfig(storage_path('app/google/credentials.json'));
    $this->client->setAccessType('offline');

    // Load token.json
    $tokenPath = storage_path('app/google/token.json');
    if (!file_exists($tokenPath)) {
      throw new \Exception('token.json not found. Please authorize the app once.');
    }

    $accessToken = json_decode(file_get_contents($tokenPath), true);
    $this->client->setAccessToken($accessToken);

    // Refresh the token if it's expired
    if ($this->client->isAccessTokenExpired()) {
      if ($this->client->getRefreshToken()) {
        $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
        file_put_contents($tokenPath, json_encode($this->client->getAccessToken()));
      } else {
        throw new \Exception('No refresh token found. Re-authentication required.');
      }
    }

    $this->gmailService = new Gmail($this->client);
  }

  public function fetchMessages($maxResults = 10,$query = null)
  {
    $messagesResponse = $this->gmailService->users_messages->listUsersMessages('me', [
      'maxResults' => $maxResults,
      'labelIds' => ['INBOX'],
      'q' => $query,
    ]);

    $messages = [];
    foreach ($messagesResponse->getMessages() as $message) {
      $msg = $this->gmailService->users_messages->get('me', $message->getId());
      $messages[] = [
        'id' => $msg->getId(),
        'headers' => $msg->getPayload()->getHeaders(),
        'snippet' => $msg->getSnippet(),
      ];
      // $messages[] = $msg;
    }

    return $messages;
  }
}
