<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\GoogleIdTokenVerifier;
use App\Services\WhatsAppNumberNormalizer;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class AuthController extends Controller
{
    public function __construct(
        private WhatsAppNumberNormalizer $whatsApp,
        private GoogleIdTokenVerifier $googleVerifier,
    ) {}

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'whatsapp_number' => ['required', 'string', 'max:32', 'unique:users,whatsapp_number'],
        ]);

        try {
            $whatsapp = $this->whatsApp->normalize($validated['whatsapp_number']);
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'whatsapp_number' => ['Format nomor WhatsApp tidak valid.'],
            ]);
        }

        $user = User::create([
            'role' => UserRole::Customer,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'whatsapp_number' => $whatsapp,
            'created_by' => $validated['name'],
            'updated_by' => $validated['name'],
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        return ApiResponse::success([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($validated)) {
            return ApiResponse::error('Email atau password salah.', 'INVALID_CREDENTIALS', 401);
        }

        /** @var User $user */
        $user = Auth::user();
        $token = $user->createToken('mobile')->plainTextToken;

        return ApiResponse::success([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function google(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        try {
            $payload = $this->googleVerifier->verify($validated['id_token']);
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 'GOOGLE_AUTH_FAILED', 401);
        }

        $googleSub = $payload['sub'];
        $email = $payload['email'] ?? null;
        $name = $payload['name'] ?? 'Google User';

        if (! $email) {
            return ApiResponse::error('Email tidak tersedia dari Google.', 'GOOGLE_AUTH_FAILED', 422);
        }

        $user = User::query()->where('google_sub', $googleSub)->first();

        if (! $user) {
            $user = User::query()->where('email', $email)->first();

            if ($user && $user->google_sub === null && $user->password !== null) {
                return ApiResponse::error(
                    'Email sudah terdaftar. Login dengan password lalu hubungkan Google di fase berikutnya.',
                    'ACCOUNT_EXISTS',
                    409,
                );
            }

            if (! $user) {
                $user = User::create([
                    'role' => UserRole::Customer,
                    'name' => $name,
                    'email' => $email,
                    'google_sub' => $googleSub,
                    'password' => null,
                    'created_by' => $name,
                    'updated_by' => $name,
                ]);
            } else {
                $user->update([
                    'google_sub' => $googleSub,
                    'name' => $user->name ?: $name,
                ]);
            }
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return ApiResponse::success([
            'user' => new UserResource($user->fresh()),
            'token' => $token,
            'requires_whatsapp' => ! $user->hasWhatsApp(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return ApiResponse::success(['message' => 'Logged out']);
    }
}
