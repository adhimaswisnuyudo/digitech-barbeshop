<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class BookingService
{
    public function __construct(
        private AvailabilityService $availability,
    ) {}

    /**
     * @param  array{branch_id: string, service_id: string, start_at: string, barber_id?: ?string, notes?: ?string, customer_id?: ?string}  $data
     */
    public function create(User $actor, array $data, bool $adminOverride = false): Booking
    {
        if (! $adminOverride && $actor->role->value === 'customer' && ! $actor->hasWhatsApp()) {
            abort(422, 'WHATSAPP_REQUIRED');
        }

        $customerId = $data['customer_id'] ?? $actor->id;

        if ($actor->role->value === 'customer' && $customerId !== $actor->id) {
            abort(403);
        }

        $service = Service::query()->where('is_active', true)->findOrFail($data['service_id']);
        Branch::query()->where('is_active', true)->findOrFail($data['branch_id']);

        $startAt = Carbon::parse($data['start_at'])->timezone(config('app.timezone'));
        $endAt = $startAt->copy()->addMinutes($service->duration_minutes);

        $barberId = $data['barber_id'] ?? null;

        return DB::transaction(function () use ($data, $service, $startAt, $endAt, $barberId, $customerId) {
            if ($barberId === null) {
                $barberId = $this->availability->findAvailableBarberAt(
                    $data['branch_id'],
                    $service,
                    $startAt,
                );

                if ($barberId === null) {
                    throw ValidationException::withMessages([
                        'start_at' => ['Slot tidak tersedia.'],
                    ]);
                }
            } else {
                if (! $this->availability->isSlotAvailable($barberId, $service, $startAt)) {
                    throw ValidationException::withMessages([
                        'start_at' => ['Slot bentrok atau tidak valid.'],
                    ]);
                }
            }

            $booking = Booking::create([
                'customer_id' => $customerId,
                'barber_id' => $barberId,
                'branch_id' => $data['branch_id'],
                'start_at' => $startAt,
                'end_at' => $endAt,
                'status' => BookingStatus::Confirmed->value,
                'payment_method' => 'pay_at_shop',
                'notes' => $data['notes'] ?? null,
            ]);

            $booking->bookingServices()->create([
                'service_id' => $service->id,
                'price_snapshot' => $service->price,
                'duration_snapshot' => $service->duration_minutes,
            ]);

            return $booking->load(['branch', 'barber.user', 'bookingServices.service', 'customer']);
        });
    }

    public function cancel(Booking $booking, User $actor, ?string $reason = null): Booking
    {
        if ($booking->status === BookingStatus::Cancelled) {
            return $booking;
        }

        if ($actor->role->value === 'customer') {
            if ($booking->customer_id !== $actor->id) {
                abort(403);
            }

            $deadline = now()->addHours(config('booking.cancel_hours_before'));
            if ($booking->start_at->lte($deadline)) {
                throw ValidationException::withMessages([
                    'start_at' => ['Booking hanya bisa dibatalkan minimal '.config('booking.cancel_hours_before').' jam sebelum jadwal.'],
                ]);
            }
        }

        $booking->update([
            'status' => BookingStatus::Cancelled->value,
            'notes' => trim(($booking->notes ?? '').($reason ? "\nCancel: {$reason}" : '')),
        ]);

        return $booking->fresh();
    }

    public function updateStatus(Booking $booking, string $status, User $actor): Booking
    {
        $allowed = match ($actor->role->value) {
            'admin' => [
                BookingStatus::InProgress->value,
                BookingStatus::Completed->value,
                BookingStatus::NoShow->value,
                BookingStatus::Cancelled->value,
            ],
            'staff' => [
                BookingStatus::InProgress->value,
                BookingStatus::Completed->value,
                BookingStatus::NoShow->value,
            ],
            default => [],
        };

        if (! in_array($status, $allowed, true)) {
            abort(403);
        }

        $booking->update(['status' => $status]);

        return $booking->fresh();
    }

    /**
     * @param  array{start_at: string, barber_id?: ?string}  $data
     */
    public function reschedule(Booking $booking, array $data): Booking
    {
        if ($booking->status === BookingStatus::Cancelled) {
            throw ValidationException::withMessages(['booking' => ['Booking sudah dibatalkan.']]);
        }

        $service = $booking->bookingServices()->first()?->service;
        if (! $service) {
            throw ValidationException::withMessages(['booking' => ['Layanan booking tidak ditemukan.']]);
        }

        $startAt = Carbon::parse($data['start_at'])->timezone(config('app.timezone'));
        $barberId = $data['barber_id'] ?? $booking->barber_id;

        if (! $this->availability->isSlotAvailable($barberId, $service, $startAt, $booking->id)) {
            throw ValidationException::withMessages([
                'start_at' => ['Slot bentrok atau tidak valid.'],
            ]);
        }

        $booking->update([
            'start_at' => $startAt,
            'end_at' => $startAt->copy()->addMinutes($service->duration_minutes),
            'barber_id' => $barberId,
            'status' => BookingStatus::Confirmed->value,
        ]);

        return $booking->fresh()->load(['branch', 'barber.user', 'bookingServices.service', 'customer']);
    }
}
