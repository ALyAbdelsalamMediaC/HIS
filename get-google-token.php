<?php

require 'vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;

$client = new Client();
$client->setAuthConfig('credentials.json');
$client->setAccessType('offline'); // To get a refresh token
$client->setPrompt('consent');
$client->addScope(Drive::DRIVE_FILE);
$client->setRedirectUri('http://localhost:8000/get-google-token.php');

if (!isset($_GET['code'])) {
    $authUrl = $client->createAuthUrl();
    echo "Open this link in your browser:<br><a href=\"$authUrl\" target=\"_blank\">$authUrl</a><br>";
}  else {
    $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (isset($accessToken['error'])) {
        echo "Error: " . $accessToken['error_description'];
    } else {
        // Save the new token to the storage/app/google-token.json file
        $tokenPath = __DIR__ . '/storage/app/google-token.json'; // Assumes script is in project root
        file_put_contents($tokenPath, json_encode($accessToken));
        header('Location: http://127.0.0.1:8000/content/upload');
        exit;
    }
}
