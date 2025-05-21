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
    echo "Open this link in your browser:\n$authUrl\n";
} else {
    $client->fetchAccessTokenWithAuthCode($_GET['code']);
    echo "Access Token:\n";
    print_r($client->getAccessToken());
}
