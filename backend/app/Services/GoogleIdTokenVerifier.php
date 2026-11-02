<?php

namespace App\Services;

use Google\Client as GoogleClient;
use RuntimeException;

final class GoogleIdTokenVerifier
{
    public function verify(string $idToken): array
    {
        $clientId = config('services.google.client_id');

        if (! $clientId) {
            throw new RuntimeException('GOOGLE_CLIENT_ID belum dikonfigurasi.');
        }

        $client = new GoogleClient(['client_id' => $clientId]);
        $payload = $client->verifyIdToken($idToken);

        if ($payload === false) {
            throw new RuntimeException('Google ID token tidak valid.');
        }

        return $payload;
    }
}
