<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\AvailabilityService;
use App\Support\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function __construct(
        private AvailabilityService $availability,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'uuid', 'exists:branches,id'],
            'service_id' => ['required', 'uuid', 'exists:services,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'barber_id' => ['nullable', 'uuid', 'exists:barbers,id'],
        ]);

        $service = Service::query()->where('is_active', true)->findOrFail($validated['service_id']);
        $date = Carbon::createFromFormat('Y-m-d', $validated['date'], config('app.timezone'));

        $slots = $this->availability->getSlots(
            $validated['branch_id'],
            $service,
            $date,
            $validated['barber_id'] ?? null,
        );

        return ApiResponse::success([
            'date' => $validated['date'],
            'slots' => $slots,
        ]);
    }
}
