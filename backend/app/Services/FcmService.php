<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class FcmService
{
    public function sendToToken(string $token, string $title, string $body, array $data = []): void
    {
        $serverKey = config('services.fcm.server_key');

        if (! $serverKey) {
            Log::info('FCM skipped (no FCM_SERVER_KEY)', compact('title', 'body', 'token'));

            return;
        }

        $response = Http::withHeaders([
            'Authorization' => 'key='.$serverKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'to' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
        ]);

        if (! $response->successful()) {
            Log::warning('FCM send failed', ['status' => $response->status(), 'body' => $response->body()]);
        }
    }
}
