<?php

namespace Tests\Feature\Api;

use App\Models\Barber;
use App\Models\Branch;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_availability_returns_slots_for_future_weekday(): void
    {
        $this->seed();

        $branch = Branch::query()->first();
        $service = Service::query()->first();

        $date = Carbon::now('Asia/Jakarta')->next('Monday')->format('Y-m-d');

        $response = $this->getJson('/api/v1/availability?'.http_build_query([
            'branch_id' => $branch->id,
            'service_id' => $service->id,
            'date' => $date,
        ]));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['date', 'slots']]);

        $this->assertNotEmpty($response->json('data.slots'));
    }

    public function test_customer_can_book_and_double_book_is_rejected(): void
    {
        $this->seed();

        $customer = User::factory()->create([
            'whatsapp_number' => '+6289998877665',
        ]);

        Sanctum::actingAs($customer);

        $branch = Branch::query()->first();
        $service = Service::query()->first();
        $barber = Barber::query()->first();

        $date = Carbon::now('Asia/Jakarta')->next('Monday');
        $startAt = $date->copy()->setTime(10, 0)->toIso8601String();

        $payload = [
            'branch_id' => $branch->id,
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'start_at' => $startAt,
        ];

        $this->postJson('/api/v1/bookings', $payload)->assertCreated();

        $this->postJson('/api/v1/bookings', $payload)
            ->assertStatus(422)
            ->assertJsonPath('code', 'BOOKING_INVALID');
    }

    public function test_booking_requires_whatsapp(): void
    {
        $this->seed();

        $customer = User::factory()->create([
            'whatsapp_number' => null,
        ]);

        Sanctum::actingAs($customer);

        $branch = Branch::query()->first();
        $service = Service::query()->first();

        $startAt = Carbon::now('Asia/Jakarta')->next('Monday')->setTime(11, 0)->toIso8601String();

        $this->postJson('/api/v1/bookings', [
            'branch_id' => $branch->id,
            'service_id' => $service->id,
            'start_at' => $startAt,
        ])
            ->assertStatus(422)
            ->assertJsonPath('code', 'WHATSAPP_REQUIRED');
    }

    public function test_staff_cannot_access_admin_dashboard(): void
    {
        $this->seed();

        $staff = User::query()->where('email', 'barber1@digitech-barbershop.test')->first();
        Sanctum::actingAs($staff);

        $this->getJson('/api/v1/admin/dashboard')->assertForbidden();
    }
}
