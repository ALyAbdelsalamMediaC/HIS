<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Videos\GoogleDriveServiceVideo;
class GoogleDriveController extends Controller
{
    protected $googleDriveService;

    public function __construct(GoogleDriveServiceVideo $googleDriveService)
    {
        $this->googleDriveService = $googleDriveService;
    }

    public function redirectToGoogle()
    {
        return redirect($this->googleDriveService->getAuthUrl());
    }

    public function handleGoogleCallback(Request $request)
    {
        $code = $request->input('code');
        if ($code) {
            $accessToken = $this->googleDriveService->getClient()->fetchAccessTokenWithAuthCode($code);
            if (isset($accessToken['error'])) {
                return "Error: " . $accessToken['error_description'];
            }
            // Display the refresh token so you can copy it
            dd("Refresh Token: " . $accessToken['refresh_token']);
        }
        return "No code received.";
    }
}
