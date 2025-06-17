<?php

use Illuminate\Support\Facades\Route;
use App\Services\GmailService;
use Illuminate\Http\Request;
use Google\Client;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/oauth2callback', function (Request $request) {
    $code = $request->get('code');

    if (!$code) {
        return response('Authorization code not provided.', 400);
    }

    $client = new Client();
    $client->setAuthConfig(storage_path('app/google/credentials.json'));
    $client->setRedirectUri(url('/oauth2callback')); // Ensure this matches the one in Google Console
    $client->setScopes(['https://www.googleapis.com/auth/gmail.readonly']);
    $client->setAccessType('offline');

    try {
        $accessToken = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($accessToken['error'])) {
            return response('Error fetching token: ' . $accessToken['error_description'], 400);
        }

        // Save token to storage
        // file_put_contents('../storage/app/google/token.json', json_encode($accessToken));

        return response($accessToken);
    } catch (\Exception $e) {
        return response('Exception: ' . $e->getMessage(), 500);
    }
});