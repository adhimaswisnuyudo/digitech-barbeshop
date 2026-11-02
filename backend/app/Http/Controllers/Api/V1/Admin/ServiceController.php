<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        return ApiResponse::success(ServiceResource::collection(Service::query()->orderBy('name')->get()));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'price' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $service = Service::create($validated);

        return ApiResponse::success(new ServiceResource($service), 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $service = Service::query()->findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['sometimes', 'integer', 'min:5', 'max:480'],
            'price' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $service->update($validated);

        return ApiResponse::success(new ServiceResource($service->fresh()));
    }

    public function destroy(string $id): JsonResponse
    {
        Service::query()->findOrFail($id)->update(['is_active' => false]);

        return ApiResponse::success(['message' => 'Layanan dinonaktifkan']);
    }
}
