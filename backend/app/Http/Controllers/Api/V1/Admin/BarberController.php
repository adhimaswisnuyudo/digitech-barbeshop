<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\BarberSchedule;
use App\Models\BarberTimeOff;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class BarberController extends Controller
{
    public function index(): JsonResponse
    {
        $barbers = Barber::query()->with(['user', 'branch'])->orderBy('created_at')->get();

        return ApiResponse::success($barbers->map(fn (Barber $b) => [
            'id' => $b->id,
            'bio' => $b->bio,
            'is_active' => $b->is_active,
            'branch_id' => $b->branch_id,
            'user' => [
                'id' => $b->user->id,
                'name' => $b->user->name,
                'email' => $b->user->email,
            ],
        ]));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'branch_id' => ['required', 'uuid', 'exists:branches,id'],
            'bio' => ['nullable', 'string'],
            'whatsapp_number' => ['required', 'string', 'unique:users,whatsapp_number'],
        ]);

        $user = User::create([
            'role' => UserRole::Staff,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'whatsapp_number' => $validated['whatsapp_number'],
            'branch_id' => $validated['branch_id'],
        ]);

        $barber = Barber::create([
            'user_id' => $user->id,
            'branch_id' => $validated['branch_id'],
            'bio' => $validated['bio'] ?? null,
        ]);

        return ApiResponse::success(['id' => $barber->id, 'user_id' => $user->id], 201);
    }

    public function updateSchedules(Request $request, string $id): JsonResponse
    {
        $barber = Barber::query()->findOrFail($id);

        $validated = $request->validate([
            'schedules' => ['required', 'array', 'min:1'],
            'schedules.*.day_of_week' => ['required', 'integer', 'between:1,7'],
            'schedules.*.start_time' => ['required', 'date_format:H:i'],
            'schedules.*.end_time' => ['required', 'date_format:H:i', 'after:schedules.*.start_time'],
        ]);

        BarberSchedule::query()->where('barber_id', $barber->id)->delete();

        foreach ($validated['schedules'] as $row) {
            BarberSchedule::create([
                'barber_id' => $barber->id,
                'day_of_week' => $row['day_of_week'],
                'start_time' => $row['start_time'].':00',
                'end_time' => $row['end_time'].':00',
            ]);
        }

        return ApiResponse::success(['message' => 'Jadwal diperbarui']);
    }

    public function storeTimeOff(Request $request, string $id): JsonResponse
    {
        $barber = Barber::query()->findOrFail($id);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $off = BarberTimeOff::create([
            'barber_id' => $barber->id,
            'date' => $validated['date'],
            'reason' => $validated['reason'] ?? null,
        ]);

        return ApiResponse::success(['id' => $off->id], 201);
    }

    public function destroyTimeOff(string $barberId, string $timeOffId): JsonResponse
    {
        BarberTimeOff::query()
            ->where('barber_id', $barberId)
            ->where('id', $timeOffId)
            ->firstOrFail()
            ->delete();

        return ApiResponse::success(['message' => 'Libur dihapus']);
    }
}
