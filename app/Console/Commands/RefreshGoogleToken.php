<?php

namespace App\Console\Commands;

use Google\Client;
use Illuminate\Console\Command;

class RefreshGoogleToken extends Command
{
    protected $signature = 'google:refresh-token';
    protected $description = 'Refresh Google Drive access token';

    public function handle()
    {
        $client = new Client();
        $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $client->setAccessType('offline');
        $client->addScope(\Google\Service\Drive::DRIVE_FILE);

        $tokenPath = storage_path('app/google-token.json');

        if (!file_exists($tokenPath)) {
            $this->error('No token found at ' . $tokenPath);
            return 1;
        }

        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);

        if ($client->isAccessTokenExpired()) {
            try {
                $refreshToken = $accessToken['refresh_token'] ?? null;
                if (!$refreshToken) {
                    $this->error('No refresh token available. Please re-authenticate.');
                    return 1;
                }

                $newAccessToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);
                if (isset($newAccessToken['error'])) {
                    $this->error('Failed to refresh token: ' . $newAccessToken['error_description']);
                    return 1;
                }

                file_put_contents($tokenPath, json_encode($newAccessToken));
                $this->info('Google Drive token refreshed successfully.');
            } catch (\Exception $e) {
                $this->error('Error refreshing token: ' . $e->getMessage());
                return 1;
            }
        } else {
            $this->info('Token is still valid.');
        }

        return 0;
    }
}