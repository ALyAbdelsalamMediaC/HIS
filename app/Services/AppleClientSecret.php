<?php

namespace App\Services;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;

class AppleClientSecret
{
    public static function generate()
    {
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::file(config('services.apple.private_key_path'))
        );

        $now = now();
        $token = $config->builder()
            ->issuedBy(config('services.apple.team_id')) // Team ID
            ->issuedAt($now)
            ->expiresAt($now->addMonths(6))
            ->permittedFor('https://appleid.apple.com') // Audience
            ->relatedTo(config('services.apple.client_id')) // Client ID
            ->withHeader('kid', config('services.apple.key_id')) // Key ID
            ->getToken($config->signer(), $config->signingKey());

        return $token->toString();
    }
}