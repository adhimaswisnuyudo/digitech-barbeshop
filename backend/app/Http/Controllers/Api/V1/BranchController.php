<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class BranchController extends Controller
{
    public function index(): JsonResponse
    {
        $branches = Branch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return ApiResponse::success(BranchResource::collection($branches));
    }

    public function show(string $id): JsonResponse
    {
        $branch = Branch::query()
            ->where('is_active', true)
            ->findOrFail($id);

        return ApiResponse::success(new BranchResource($branch));
    }
}
