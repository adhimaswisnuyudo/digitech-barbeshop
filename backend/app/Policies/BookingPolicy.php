<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        return match ($user->role->value) {
            'admin' => true,
            'customer' => $booking->customer_id === $user->id,
            'staff' => $user->branch_id === $booking->branch_id
                || $booking->barber?->user_id === $user->id,
            default => false,
        };
    }

    public function cancel(User $user, Booking $booking): bool
    {
        if ($user->role->value === 'admin') {
            return true;
        }

        return $user->role->value === 'customer' && $booking->customer_id === $user->id;
    }

    public function updateStatus(User $user, Booking $booking): bool
    {
        if ($user->role->value === 'admin') {
            return true;
        }

        if ($user->role->value !== 'staff') {
            return false;
        }

        return $booking->barber?->user_id === $user->id
            || $user->branch_id === $booking->branch_id;
    }
}
