<?php

require 'vendor/autoload.php';
$client = new Google\Client();
$client->setAuthConfig('storage/app/google/credentials.json');
$client->setScopes([Google\Service\Gmail::GMAIL_READONLY]);
$client->setRedirectUri('http://localhost:8080/oauth2callback'); // For command-line usage
$client->setAccessType('offline');
$client->setPrompt('select_account consent');

$authUrl = $client->createAuthUrl();
echo "Open the following link in your browser:\n$authUrl\n\n";
echo "Enter the authorization code here:\n";
$authCode = trim(fgets(STDIN));

// Exchange code for access token
$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

if (array_key_exists('error', $accessToken)) {
    throw new Exception(join(', ', $accessToken));
}
echo "Access Token:\n";
echo json_encode($accessToken, JSON_PRETTY_PRINT) . "\n";

file_put_contents('storage/app/google/token.json', json_encode($accessToken));
echo "Token saved to storage/app/google/token.json\n";