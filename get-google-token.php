<?php
require __DIR__ . '/vendor/autoload.php';

use Google\Client;

$client = new Client();
$client->setAuthConfig(__DIR__ . '/storage/app/credentials.json');
$client->setRedirectUri('https://his.mc-apps.org/get-google-token.php');
$client->setAccessType('offline');
$client->setScopes([\Google\Service\Drive::DRIVE_FILE]);

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (isset($token['error'])) {
        die('Error fetching token: ' . $token['error_description']);
    }
    $token['created'] = time();
    file_put_contents(__DIR__ . '/storage/app/google-token.json', json_encode($token, JSON_PRETTY_PRINT));
    echo 'Token saved successfully. You can close this window.';
} else {
    header('Location: ' . $client->createAuthUrl());
    exit;
}
?>