<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_and_receive_uuid(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Pelanggan Test',
            'email' => 'customer@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'whatsapp_number' => '081234567890',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.role', 'customer')
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'created_by', 'updated_by'],
                    'token',
                ],
            ]);

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f-]{36}$/i',
            $response->json('data.user.id'),
        );

        $this->assertDatabaseHas('users', [
            'email' => 'customer@example.com',
            'whatsapp_number' => '+6281234567890',
            'created_by' => 'Pelanggan Test',
        ]);
    }

    public function test_customer_can_login(): void
    {
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_profile_requires_authentication(): void
    {
        $this->getJson('/api/v1/profile')->assertUnauthorized();
    }
}
