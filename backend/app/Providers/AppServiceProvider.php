<?php

namespace App\Providers;

use App\Models\Barber;
use App\Models\Booking;
use App\Policies\BarberPolicy;
use App\Policies\BookingPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Barber::class, BarberPolicy::class);
        Gate::policy(Booking::class, BookingPolicy::class);
    }
}
