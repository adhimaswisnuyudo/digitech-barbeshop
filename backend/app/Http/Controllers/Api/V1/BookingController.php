<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Jobs\SendBookingConfirmedPush;
use App\Models\Booking;
use App\Models\User;
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

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $query = Booking::query()
            ->with(['branch', 'barber.user', 'customer', 'bookingServices.service'])
            ->orderByDesc('start_at');

        match ($user->role->value) {
            'customer' => $query->where('customer_id', $user->id),
            'staff' => $query->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                    ->orWhereHas('barber', fn ($b) => $b->where('user_id', $user->id));
            }),
            'admin' => null,
            default => abort(403),
        };

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('start_at', $request->string('date'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->string('branch_id'));
        }

        $paginated = $query->paginate(15);

        return ApiResponse::success([
            'items' => BookingResource::collection($paginated->items()),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->role->value !== 'customer' && $user->role->value !== 'admin') {
            abort(403);
        }

        if ($user->role->value === 'customer' && ! $user->hasWhatsApp()) {
            return ApiResponse::error(
                'Nomor WhatsApp wajib diisi sebelum booking.',
                'WHATSAPP_REQUIRED',
                422,
            );
        }

        $validated = $request->validate([
            'branch_id' => ['required', 'uuid', 'exists:branches,id'],
            'service_id' => ['required', 'uuid', 'exists:services,id'],
            'start_at' => ['required', 'date'],
            'barber_id' => ['nullable', 'uuid', 'exists:barbers,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'customer_id' => ['nullable', 'uuid', 'exists:users,id'],
        ]);

        try {
            $booking = $this->bookings->create(
                $user,
                $validated,
                adminOverride: $user->role->value === 'admin',
            );
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

    public function show(Request $request, string $id): JsonResponse
    {
        $booking = Booking::query()
            ->with(['branch', 'barber.user', 'customer', 'bookingServices.service'])
            ->findOrFail($id);

        $this->authorize('view', $booking);

        return ApiResponse::success(new BookingResource($booking));
    }

    public function cancel(Request $request, string $id): JsonResponse
    {
        $booking = Booking::query()->findOrFail($id);
        $this->authorize('cancel', $booking);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $booking = $this->bookings->cancel($booking, $request->user(), $validated['reason'] ?? null);
        } catch (ValidationException $e) {
            return ApiResponse::error(
                collect($e->errors())->flatten()->first() ?? 'Validasi gagal.',
                'CANCEL_INVALID',
                422,
                $e->errors(),
            );
        }

        return ApiResponse::success(new BookingResource($booking->load(['branch', 'barber.user', 'customer', 'bookingServices.service'])));
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $booking = Booking::query()->findOrFail($id);
        $this->authorize('updateStatus', $booking);

        $validated = $request->validate([
            'status' => ['required', 'in:in_progress,completed,no_show,cancelled'],
        ]);

        $booking = $this->bookings->updateStatus($booking, $validated['status'], $request->user());

        return ApiResponse::success(new BookingResource($booking->load(['branch', 'barber.user', 'customer', 'bookingServices.service'])));
    }
}
