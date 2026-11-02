<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Jobs\SendBookingConfirmedPush;
use App\Models\Booking;
use App\Services\BookingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookings,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'uuid', 'exists:users,id'],
            'branch_id' => ['required', 'uuid', 'exists:branches,id'],
            'service_id' => ['required', 'uuid', 'exists:services,id'],
            'start_at' => ['required', 'date'],
            'barber_id' => ['nullable', 'uuid', 'exists:barbers,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $booking = $this->bookings->create($request->user(), $validated, adminOverride: true);
        } catch (ValidationException $e) {
            return ApiResponse::error(
                collect($e->errors())->flatten()->first() ?? 'Validasi gagal.',
                'BOOKING_INVALID',
                422,
                $e->errors(),
            );
        }

        SendBookingConfirmedPush::dispatch($booking->id);

        return ApiResponse::success(new BookingResource($booking), 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $booking = Booking::query()->findOrFail($id);

        $validated = $request->validate([
            'start_at' => ['required', 'date'],
            'barber_id' => ['nullable', 'uuid', 'exists:barbers,id'],
        ]);

        try {
            $booking = $this->bookings->reschedule($booking, $validated);
        } catch (ValidationException $e) {
            return ApiResponse::error(
                collect($e->errors())->flatten()->first() ?? 'Validasi gagal.',
                'RESCHEDULE_INVALID',
                422,
                $e->errors(),
            );
        }

        return ApiResponse::success(new BookingResource($booking));
    }
}
