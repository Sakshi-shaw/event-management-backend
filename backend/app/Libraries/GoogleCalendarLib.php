<?php
namespace App\Libraries;

use Google\Client;
use Google\Service\Calendar;

class GoogleCalendarLib
{
    private $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuthConfig(WRITEPATH . 'credentials.json');
        $this->client->setScopes(Calendar::CALENDAR);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');

        // Load the previously saved access token
        $tokenPath = WRITEPATH . 'token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $this->client->setAccessToken($accessToken);
        }

        // If the token is expired or missing, authenticate
        if ($this->client->isAccessTokenExpired()) {
            if ($this->client->getRefreshToken()) {
                // Refresh the token if refresh token is available
                $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
            } else {
                // Otherwise, prompt the user for authentication
                $this->authenticateUser($tokenPath);
            }
        }
    }

    public function getClient()
    {
        return $this->client;
    }

    private function authenticateUser($tokenPath)
    {
        $authUrl = $this->client->createAuthUrl();
        echo "Open the following link in your browser to authenticate:\n$authUrl\n";
        echo "Enter the authorization code:\n";
        $authCode = trim(fgets(STDIN));

        // Exchange the authorization code for an access token
        $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);
        $this->client->setAccessToken($accessToken);

        // Save the access token for future use
        file_put_contents($tokenPath, json_encode($this->client->getAccessToken()));
    }
}
