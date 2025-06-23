<?php

namespace App\Services;

use Google\Client;
use Google\Service\Gmail;
use Illuminate\Support\Facades\Log;

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

  public function fetchMessages($maxResults = 10, $query = null, $folder = 'INBOX')
  {
    $messagesResponse = $this->gmailService->users_messages->listUsersMessages('me', [
      'maxResults' => $maxResults,
      'labelIds' => [$folder],
      'q' => $query,
    ]);

    $messages = [];
    foreach ($messagesResponse->getMessages() as $message) {
      $msg = $this->gmailService->users_messages->get('me', $message->getId());
      $messages[] = [
        'id' => $msg->getId(),
        'headers' => $msg->getPayload()->getHeaders(),
        'body' => $this->getTextBody($msg->getPayload()),
        'attachments' => $this->getAttachments($msg->getPayload(), $message->getId()),
        'snippet' => $msg->getSnippet(),
      ];
      // $messages[] = $msg;
      $body = $this->getTextBody($msg->getPayload());
    }

    return $messages;
  }

  private function getTextBody($payload)
  {
    $body = $payload->getBody();
    $data = $body->getData();

    if ($data && $payload->getMimeType() === 'text/html') {
      return base64_decode(strtr($data, '-_', '+/'));
    }

    return $this->extractHtmlFromParts($payload->getParts());
  }

  private function extractHtmlFromParts($parts)
  {
    $html = null;
    $plain = null;

    foreach ($parts as $part) {
      $mimeType = $part->getMimeType();
      $body = $part->getBody();
      $data = $body->getData();

      if ($data) {
        $decoded = base64_decode(strtr($data, '-_', '+/'));

        if ($mimeType === 'text/html') {
          return $decoded; // Prefer HTML, return immediately
        } elseif ($mimeType === 'text/plain' && $plain === null) {
          $plain = $decoded; // Keep plain as fallback
        }
      }

      // Recursively check nested parts
      if ($part->getParts()) {
        $result = $this->extractHtmlFromParts($part->getParts());
        if ($result) {
          return $result;
        }
      }
    }

    return $plain; // fallback to plain if no HTML found
  }

  private function extractTextFromParts($parts)
  {
    foreach ($parts as $part) {
      $mimeType = $part->getMimeType();
      $body = $part->getBody();
      $data = $body->getData();

      if ($mimeType === 'text/plain' || $mimeType === 'text/html') {
        return base64_decode(strtr($data, '-_', '+/'));
      }

      // Recursively look in nested parts
      if ($part->getParts()) {
        $result = $this->extractTextFromParts($part->getParts());
        if ($result) {
          return $result;
        }
      }
    }

    return null;
  }

  private function getAttachments($payload, $messageId)
  {
    $attachments = [];

    $parts = $payload->getParts();
    if (!$parts) return $attachments;

    foreach ($parts as $part) {
      if ($part->getFilename() && $part->getBody()->getAttachmentId()) {
        $attachmentId = $part->getBody()->getAttachmentId();
        $attachment = $this->gmailService->users_messages_attachments->get('me', $messageId, $attachmentId);
        $data = $attachment->getData();

        $decodedData = base64_decode(strtr($data, '-_', '+/'));
        $filename = $part->getFilename();
        $fileExtension = pathinfo($filename, PATHINFO_EXTENSION);
        $contentType = $part->getMimeType();

        $attachments[] = [
          'file_name' => $filename,
          'file_extension' => $fileExtension,
          'content_type' => $contentType,
          'data' => $decodedData,
        ];
      }

      // Look into nested parts
      if ($part->getParts()) {
        $attachments = array_merge($attachments, $this->getAttachmentsFromParts($part->getParts(), $messageId));
      }
    }

    return $attachments;
  }

  private function getAttachmentsFromParts($parts, $messageId)
  {
    $attachments = [];

    foreach ($parts as $part) {
      if ($part->getFilename() && $part->getBody()->getAttachmentId()) {
        $attachmentId = $part->getBody()->getAttachmentId();
        $attachment = $this->gmailService->users_messages_attachments->get('me', $messageId, $attachmentId);
        $data = $attachment->getData();

        $decodedData = base64_decode(strtr($data, '-_', '+/'));
        $filename = $part->getFilename();
        $fileExtension = pathinfo($filename, PATHINFO_EXTENSION);
        $contentType = $part->getMimeType();

        $attachments[] = [
          'file_name' => $filename,
          'file_extension' => $fileExtension,
          'content_type' => $contentType,
          'data' => $decodedData,
        ];
      }

      if ($part->getParts()) {
        $attachments = array_merge($attachments, $this->getAttachmentsFromParts($part->getParts(), $messageId));
      }
    }

    return $attachments;
  }
}
