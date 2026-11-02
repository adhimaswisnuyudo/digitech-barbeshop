<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\WhatsAppNumberNormalizer;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProfileController extends Controller
{
    public function __construct(
        private WhatsAppNumberNormalizer $whatsApp,
    ) {}

    public function show(Request $request): JsonResponse
    {
        return ApiResponse::success(new UserResource($request->user()));
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'whatsapp_number' => [
                'sometimes',
                'string',
                'max:32',
                Rule::unique('users', 'whatsapp_number')->ignore($user->id),
            ],
        ]);

        if (array_key_exists('whatsapp_number', $validated)) {
            try {
                $validated['whatsapp_number'] = $this->whatsApp->normalize($validated['whatsapp_number']);
            } catch (Throwable) {
                throw ValidationException::withMessages([
                    'whatsapp_number' => ['Format nomor WhatsApp tidak valid.'],
                ]);
            }
        }

        $user->update($validated);

        return ApiResponse::success(new UserResource($user->fresh()));
    }
}
