<?php

namespace App\Http\Controllers;

use Google\Client;
use Google\Service\Drive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleAuthController extends Controller
{
    public function getGoogleToken(Request $request)
    {
        $client = new Client();
        $client->setAuthConfig(base_path('credentials.json'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->addScope(Drive::DRIVE_FILE);
        $client->setRedirectUri(env('GOOGLE_DRIVE_REDIRECT_URI', 'https://his.mc-apps.org/get-google-token.php'));

        if (!$request->has('code')) {
            $authUrl = $client->createAuthUrl();
            return view('google-auth', ['authUrl' => $authUrl]);
        }

        try {
            $accessToken = $client->fetchAccessTokenWithAuthCode($request->query('code'));
            if (isset($accessToken['error'])) {
                return view('google-auth', ['error' => 'Error: ' . $accessToken['error_description']]);
            }

            // Save the new token to storage/app/google-token.json
            $tokenPath = storage_path('app/google-token.json');
            if (!is_dir(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0755, true);
            }
            file_put_contents($tokenPath, json_encode($accessToken));

            return redirect()->route('content.videos'); // Adjust to your route
        } catch (\Exception $e) {
            Log::error('Google token retrieval failed: ' . $e->getMessage());
            return view('google-auth', ['error' => 'An error occurred during authentication.']);
        }
    }
}