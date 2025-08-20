<?php

namespace App\Services;

use DateTimeImmutable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;

class AppleClientSecret
{
    public static function generate()
    {
        $privateKeyPath = Config::get('services.apple.private_key_path');

        // Load private key content
        $privateKeyContent = file_get_contents($privateKeyPath);
        if ($privateKeyContent === false) {
            throw new \Exception('Unable to read private key file.');
        }
        
        // Load the private key resource
        $privateKeyResource = openssl_pkey_get_private($privateKeyContent);
        if ($privateKeyResource === false) {
            throw new \Exception('Invalid private key: ' . openssl_error_string());
        }
        
        // Derive the public key
        $keyDetails = openssl_pkey_get_details($privateKeyResource);
        if ($keyDetails === false) {
            throw new \Exception('Unable to get key details: ' . openssl_error_string());
        }
        $publicKeyContent = $keyDetails['key'];
        
        // Free the resource (optional, but good practice)
        openssl_pkey_free($privateKeyResource);
        
        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($privateKeyContent),
            InMemory::plainText($publicKeyContent)
        );
        
        $now = new DateTimeImmutable();
        return $config->builder()
            ->issuedBy(env('APPLE_TEAM_ID'))
            ->permittedFor('https://appleid.apple.com')
            ->issuedAt($now)
            ->expiresAt($now->modify('+6 months'))
            ->relatedTo(env('APPLE_CLIENT_ID'))
            ->withHeader('kid', env('APPLE_KEY_ID'))
            ->getToken($config->signer(), $config->signingKey())
            ->toString();
    }
}