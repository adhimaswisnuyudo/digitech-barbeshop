<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(): JsonResponse
    {
        return ApiResponse::success(BranchResource::collection(Branch::query()->orderBy('name')->get()));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateBranch($request);

        $branch = Branch::create($validated);

        return ApiResponse::success(new BranchResource($branch), 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $branch = Branch::query()->findOrFail($id);
        $validated = $this->validateBranch($request, $branch->id);

        $branch->update($validated);

        return ApiResponse::success(new BranchResource($branch->fresh()));
    }

    public function destroy(string $id): JsonResponse
    {
        $branch = Branch::query()->findOrFail($id);
        $branch->update(['is_active' => false]);

        return ApiResponse::success(['message' => 'Cabang dinonaktifkan']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateBranch(Request $request, ?string $id = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'opening_hours' => ['required', 'array'],
            'timezone' => ['sometimes', 'string', 'max:64'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['timezone'] ??= 'Asia/Jakarta';
        $validated['is_active'] ??= true;

        return $validated;
    }
}
