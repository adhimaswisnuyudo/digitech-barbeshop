<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Confirmed = 'confirmed';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    /**
     * @return list<string>
     */
    public static function blockingAvailability(): array
    {
        return [
            self::Confirmed->value,
            self::InProgress->value,
        ];
    }
}
