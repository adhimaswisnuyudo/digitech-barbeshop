<?php

namespace App\Policies;

use App\Models\Barber;
use App\Models\User;

class BarberPolicy
{
    public function view(User $user, Barber $barber): bool
    {
        if ($user->role->value === 'admin') {
            return true;
        }

        if ($user->role->value === 'staff') {
            return $user->branch_id === $barber->branch_id
                || $user->barber?->id === $barber->id;
        }

        return false;
    }
}
