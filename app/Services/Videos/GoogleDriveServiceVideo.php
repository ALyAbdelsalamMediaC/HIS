<?php

namespace App\Services\Videos;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Log;

class GoogleDriveServiceVideo
{
    protected $client;
    protected $service;

    public function __construct()
    {
        $this->client = new Client();
        $credentialsPath = storage_path('app/credentials.json');

        if (!file_exists($credentialsPath)) {
            throw new \Exception('Credentials file not found at: ' . $credentialsPath);
        }

        $credentials = json_decode(file_get_contents($credentialsPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON in credentials file: ' . json_last_error_msg());
        }

        if (!isset($credentials['web']['client_id']) || !isset($credentials['web']['client_secret'])) {
            throw new \Exception('Client ID or Client Secret missing in credentials.json');
        }

        $this->client->setClientId($credentials['web']['client_id']);
        $this->client->setClientSecret($credentials['web']['client_secret']);
        $this->client->setAccessType('offline');
        $this->client->setScopes([Drive::DRIVE_FILE]);
        $this->client->setRedirectUri('https://his.mc-apps.org/get-google-token.php');

        // Load existing token
        $tokenPath = storage_path('app/google-token.json');

        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid JSON in google-token.json: ' . json_last_error_msg());
                throw new \Exception('Invalid JSON in google-token.json');
            }
            $this->client->setAccessToken($accessToken);
        } else {
            Log::warning('No token file found at: ' . $tokenPath);
            throw new \Exception('No token file found. Please authenticate to generate a new token.');
        }

        // Refresh token if expired
        if ($this->client->isAccessTokenExpired()) {
            try {
                if (!isset($accessToken['refresh_token'])) {
                    throw new \Exception('No refresh token available in google-token.json');
                }

                $newAccessToken = $this->client->fetchAccessTokenWithRefreshToken($accessToken['refresh_token']);
                
                if (isset($newAccessToken['error'])) {
                    Log::error('Failed to refresh access token: ' . $newAccessToken['error_description']);
                    throw new \Exception('Failed to refresh access token: ' . $newAccessToken['error_description']);
                }

                // Update created time and save the new token
                $newAccessToken['created'] = time();
                $this->client->setAccessToken($newAccessToken);
                
                // Save the new token to file
                if (file_put_contents($tokenPath, json_encode($newAccessToken, JSON_PRETTY_PRINT)) === false) {
                    Log::error('Failed to write new token to: ' . $tokenPath);
                    throw new \Exception('Failed to save new access token to file');
                }

                Log::info('Access token refreshed and saved successfully');
            } catch (\Exception $e) {
                Log::error('Token refresh failed: ' . $e->getMessage());
                throw new \Exception('Unable to refresh access token: ' . $e->getMessage());
            }
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

    public function uploadFile($file, $name)
    {
        $fileMetadata = new DriveFile([
            'name' => $name,
            'parents' => [env('GOOGLE_DRIVE_FOLDER_V_VIDEOS')],
        ]);

        $service = $this->service;

        $filePath = $file->getRealPath();
        $mimeType = $file->getMimeType();

        // Read the file contents as a string for Google API
        $fileContents = file_get_contents($filePath);
        if ($fileContents === false) {
            throw new \Exception('Failed to read file contents for Google Drive upload.');
        }
        $uploadedFile = $service->files->create(
            $fileMetadata,
            [
                'data' => $fileContents,
                'mimeType' => $mimeType,
                'uploadType' => 'resumable',
                'fields' => 'id',
            ]
        );

        if (!$uploadedFile || !isset($uploadedFile->id)) {
            throw new \Exception('Failed to upload file to Google Drive.');
        }

        // Make the file public
        $permission = new \Google\Service\Drive\Permission();
        $permission->setRole('reader');
        $permission->setType('anyone');
        $this->service->permissions->create($uploadedFile->id, $permission);

        return $uploadedFile->id;
    }

    public function uploadPdf($file, $name)
    {
        $fileMetadata = new DriveFile([
            'name' => $name,
            'parents' => [env('GOOGLE_DRIVE_PDF_FOLDER_ID')], //  Upload to specific folder

        ]);

        $content = file_get_contents($file->getRealPath());

        $uploadedFile = $this->service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $file->getMimeType(),
            'uploadType' => 'multipart',
            'fields' => 'id'
        ]);

        // Make the file public
        $permission = new \Google\Service\Drive\Permission();
        $permission->setRole('reader');
        $permission->setType('anyone');

        $this->service->permissions->create($uploadedFile->id, $permission);

        return $uploadedFile->id;
    }

    public function getFileIdFromUrl($url)
    {
        // Extract file ID from Google Drive URL
        if (preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public function deleteFile($fileId)
    {
        try {
            $this->service->files->delete($fileId);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
