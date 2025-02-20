<?php
require 'vendor/autoload.php';

$client = new Google\Client();
$client->setAuthConfig(__DIR__ . '/writable/credentials.json');
$client->setScopes(Google\Service\Calendar::CALENDAR);
$client->setAccessType('offline');
$client->setPrompt('select_account consent');

$tokenPath = __DIR__ . '/writable/token.json';

// Check if token.json already exists
if (file_exists($tokenPath)) {
    echo "Token already exists. Delete `token.json` and retry if you need a new one.\n";
    exit;
}

// Get the authorization URL   
$authUrl = $client->createAuthUrl();
echo "Open this URL in your browser to authorize the app:\n$authUrl\n";
echo "Enter the authorization code: ";
$authCode = trim(fgets(STDIN));

// Exchange authorization code for access token
$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
$client->setAccessToken($accessToken);

// Save the token to a file
file_put_contents($tokenPath, json_encode($client->getAccessToken()));

echo "Access token saved to token.json\n";
