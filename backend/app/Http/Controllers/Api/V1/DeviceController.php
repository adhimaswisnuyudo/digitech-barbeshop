<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => ['required', 'string', 'max:512'],
            'platform' => ['required', 'in:ios,android'],
        ]);

        $device = UserDevice::query()->updateOrCreate(
            ['fcm_token' => $validated['fcm_token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $validated['platform'],
            ],
        );

        return ApiResponse::success(['id' => $device->id], 201);
    }
}
