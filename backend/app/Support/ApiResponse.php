<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    public static function success(mixed $data, int $status = 200): JsonResponse
    {
        return response()->json(['data' => $data], $status);
    }

    public static function error(
        string $message,
        string $code,
        int $status = 400,
        ?array $errors = null,
    ): JsonResponse {
        $payload = [
            'message' => $message,
            'code' => $code,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
