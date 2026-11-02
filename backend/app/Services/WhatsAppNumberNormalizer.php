<?php

namespace App\Services;

use InvalidArgumentException;

final class WhatsAppNumberNormalizer
{
    public function normalize(?string $input): ?string
    {
        if ($input === null || trim($input) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $input) ?? '';

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        if (str_starts_with($digits, '62')) {
            // ok
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        } else {
            throw new InvalidArgumentException('Format nomor WhatsApp tidak valid.');
        }

        if (strlen($digits) < 10 || strlen($digits) > 15) {
            throw new InvalidArgumentException('Format nomor WhatsApp tidak valid.');
        }

        return '+'.$digits;
    }
}
