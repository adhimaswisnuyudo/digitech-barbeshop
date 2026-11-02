<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Barber;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class AvailabilityService
{
    /** @var array<int, string> */
    private const DAY_KEYS = [
        1 => 'monday',
        2 => 'tuesday',
        3 => 'wednesday',
        4 => 'thursday',
        5 => 'friday',
        6 => 'saturday',
        7 => 'sunday',
    ];

    /**
     * @return list<array{start_at: string, end_at: string, barber_id: string}>
     */
    public function getSlots(
        string $branchId,
        Service $service,
        Carbon $date,
        ?string $barberId = null,
    ): array {
        $branch = Branch::query()->where('is_active', true)->findOrFail($branchId);
        $date = $date->copy()->timezone($branch->timezone)->startOfDay();

        $barbers = Barber::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->when($barberId, fn ($q) => $q->where('id', $barberId))
            ->with(['schedules'])
            ->get();

        $slots = [];

        foreach ($barbers as $barber) {
            $windows = $this->workingWindows($branch, $barber, $date);
            if ($windows === []) {
                continue;
            }

            $bookings = $this->bookingsForBarberOnDate($barber->id, $date);

            foreach ($windows as [$windowStart, $windowEnd]) {
                $cursor = $windowStart->copy();
                $stepMinutes = 15;

                while ($cursor->copy()->addMinutes($service->duration_minutes)->lte($windowEnd)) {
                    if ($this->slotIsFree($cursor, $service, $bookings)) {
                        $end = $cursor->copy()->addMinutes($service->duration_minutes);
                        $slots[] = [
                            'start_at' => $cursor->toIso8601String(),
                            'end_at' => $end->toIso8601String(),
                            'barber_id' => $barber->id,
                        ];
                    }
                    $cursor->addMinutes($stepMinutes);
                }
            }
        }

        usort($slots, fn ($a, $b) => strcmp($a['start_at'], $b['start_at']));

        return $slots;
    }

    public function isSlotAvailable(
        string $barberId,
        Service $service,
        Carbon $startAt,
        ?string $ignoreBookingId = null,
    ): bool {
        $barber = Barber::query()->with('branch')->findOrFail($barberId);
        $startAt = $startAt->copy()->timezone($barber->branch->timezone);
        $date = $startAt->copy()->startOfDay();

        $windows = $this->workingWindows($barber->branch, $barber, $date);
        $endAt = $startAt->copy()->addMinutes($service->duration_minutes);

        $insideWindow = false;
        foreach ($windows as [$windowStart, $windowEnd]) {
            if ($startAt->gte($windowStart) && $endAt->lte($windowEnd)) {
                $insideWindow = true;
                break;
            }
        }

        if (! $insideWindow) {
            return false;
        }

        $bookings = $this->bookingsForBarberOnDate($barberId, $date, $ignoreBookingId);

        return $this->slotIsFree($startAt, $service, $bookings);
    }

    public function findAvailableBarberAt(string $branchId, Service $service, Carbon $startAt): ?string
    {
        $barbers = Barber::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->pluck('id');

        foreach ($barbers as $id) {
            if ($this->isSlotAvailable($id, $service, $startAt)) {
                return $id;
            }
        }

        return null;
    }

    /**
     * @return list{array{0: Carbon, 1: Carbon}}
     */
    private function workingWindows(Branch $branch, Barber $barber, Carbon $date): array
    {
        $dayKey = self::DAY_KEYS[$date->dayOfWeekIso] ?? null;
        if (! $dayKey) {
            return [];
        }

        $hours = $branch->opening_hours[$dayKey] ?? null;
        if (! $hours || ! empty($hours['closed'])) {
            return [];
        }

        if ($barber->timeOff()->whereDate('date', $date->toDateString())->exists()) {
            return [];
        }

        $schedule = $barber->schedules->firstWhere('day_of_week', $date->dayOfWeekIso);
        if (! $schedule) {
            return [];
        }

        $branchOpen = $date->copy()->setTimeFromTimeString($hours['open']);
        $branchClose = $date->copy()->setTimeFromTimeString($hours['close']);
        $barberStart = $date->copy()->setTimeFromTimeString(substr((string) $schedule->start_time, 0, 5));
        $barberEnd = $date->copy()->setTimeFromTimeString(substr((string) $schedule->end_time, 0, 5));

        $start = $branchOpen->max($barberStart);
        $end = $branchClose->min($barberEnd);

        if ($start->gte($end)) {
            return [];
        }

        return [[$start, $end]];
    }

    /**
     * @return Collection<int, Booking>
     */
    private function bookingsForBarberOnDate(string $barberId, Carbon $date, ?string $ignoreId = null): Collection
    {
        return Booking::query()
            ->where('barber_id', $barberId)
            ->whereDate('start_at', $date->toDateString())
            ->whereIn('status', BookingStatus::blockingAvailability())
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->get();
    }

    /**
     * @param  Collection<int, Booking>  $bookings
     */
    private function slotIsFree(Carbon $start, Service $service, Collection $bookings): bool
    {
        if ($start->lt(now())) {
            return false;
        }

        $end = $start->copy()->addMinutes($service->duration_minutes);
        $buffer = (int) config('booking.buffer_minutes');

        foreach ($bookings as $booking) {
            $blockedStart = $booking->start_at->copy()->subMinutes($buffer);
            $blockedEnd = $booking->end_at->copy()->addMinutes($buffer);

            if ($start->lt($blockedEnd) && $end->gt($blockedStart)) {
                return false;
            }
        }

        return true;
    }
}
