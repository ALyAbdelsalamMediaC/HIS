<?php

namespace App\Services\Videos;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class GoogleDriveServicePDF
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

            //     if ($this->client->isAccessTokenExpired()) {
            //     header('Location: http://localhost:8000/get-google-token.php');
            //     exit;
            // }
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
        $this->client->setRedirectUri('http://localhost:8000/get-google-token.php'); // Replace with your redirect URI
        return $this->client->createAuthUrl();
    }
    public function getClient()
    {
        return $this->client;
    }



    public function uploadPdf($file, $name)
    {
        $fileMetadata = new DriveFile([
            'name' => $name,
            'parents' => [env('GOOGLE_DRIVE_FOLDER_V_PDFS')], //  Upload to specific folder

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

        return "https://lh3.googleusercontent.com/d/{$uploadedFile->id}=w1000?authuser=0";

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
