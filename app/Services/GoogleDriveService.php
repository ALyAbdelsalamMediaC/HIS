<?php

namespace App\Services\Videos;

use Google\Client;
use Google\Service\Drive;

class GoogleDriveService
{
    protected $client;
    protected $service;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $this->client->setAccessType('offline');
        $this->client->setScopes([Drive::DRIVE_FILE]);

        // Check if a token file exists
        $tokenPath = storage_path('app/google-token.json');

        if (file_exists($tokenPath)) {

            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $this->client->setAccessToken($accessToken);
        }

        // If the access token is expired, refresh it
        if ($this->client->isAccessTokenExpired()) {

                if ($this->client->isAccessTokenExpired()) {
                header('Location: https://his.mc-apps.org/get-google-token.php');
                exit;
            }
            $accessToken = $this->client->fetchAccessTokenWithRefreshToken($accessToken['refresh_token']);
            if (isset($accessToken['error'])) {
                throw new \Exception("Failed to refresh access token: " . $accessToken['error_description']);
            }

            $this->client->setAccessToken($accessToken);

            // Save the new token to file
            file_put_contents($tokenPath, json_encode($this->client->getAccessToken()));
        }

        $this->service = new Drive($this->client);
    }

    
    public function getAuthUrl()
    {
        $this->client->setRedirectUri('https://his.mc-apps.org/get-google-token.php'); // Replace with your redirect URI
        return $this->client->createAuthUrl();
    }
    public function getClient()
    {
        return $this->client;
    }

   
}
