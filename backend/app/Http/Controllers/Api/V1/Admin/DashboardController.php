<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Support\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $today = Carbon::today(config('app.timezone'));

        $bookingsToday = Booking::query()
            ->whereDate('start_at', $today)
            ->where('status', '!=', BookingStatus::Cancelled->value)
            ->count();

        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();

        $weekBookings = Booking::query()
            ->whereBetween('start_at', [$weekStart, $weekEnd])
            ->get();

        $confirmedOrDone = $weekBookings->whereIn('status', [
            BookingStatus::Confirmed->value,
            BookingStatus::InProgress->value,
            BookingStatus::Completed->value,
            BookingStatus::NoShow->value,
        ]);

        $completed = $weekBookings->where('status', BookingStatus::Completed->value)->count();
        $noShow = $weekBookings->where('status', BookingStatus::NoShow->value)->count();
        $denominator = $completed + $noShow;
        $noShowRate = $denominator > 0 ? round($noShow / $denominator, 4) : 0.0;

        $bookedMinutes = $confirmedOrDone->sum(fn (Booking $b) => $b->start_at->diffInMinutes($b->end_at));

        return ApiResponse::success([
            'bookings_today' => $bookingsToday,
            'week' => [
                'start' => $weekStart->toDateString(),
                'end' => $weekEnd->toDateString(),
                'bookings_count' => $confirmedOrDone->count(),
                'booked_minutes' => $bookedMinutes,
                'no_show_rate' => $noShowRate,
            ],
        ]);
    }
}
