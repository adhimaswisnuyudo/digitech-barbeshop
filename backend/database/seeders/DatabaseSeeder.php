<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Barber;
use App\Models\BarberSchedule;
use App\Models\Branch;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'role' => UserRole::Admin,
            'name' => 'Admin Digitech',
            'email' => 'admin@digitech-barbershop.test',
            'password' => Hash::make('password'),
            'whatsapp_number' => '+6281234567000',
            'created_by' => 'System',
            'updated_by' => 'System',
        ]);

        $branch = Branch::create([
            'name' => 'Digitech Barbershop Pusat',
            'address' => 'Jl. Contoh No. 1, Jakarta',
            'latitude' => -6.2000000,
            'longitude' => 106.8166660,
            'opening_hours' => [
                'monday' => ['open' => '09:00', 'close' => '21:00'],
                'tuesday' => ['open' => '09:00', 'close' => '21:00'],
                'wednesday' => ['open' => '09:00', 'close' => '21:00'],
                'thursday' => ['open' => '09:00', 'close' => '21:00'],
                'friday' => ['open' => '09:00', 'close' => '21:00'],
                'saturday' => ['open' => '09:00', 'close' => '20:00'],
                'sunday' => ['closed' => true],
            ],
            'timezone' => 'Asia/Jakarta',
            'is_active' => true,
            'created_by' => $admin->name,
            'updated_by' => $admin->name,
        ]);

        $services = [
            ['name' => 'Potong rambut', 'description' => 'Potong standar', 'duration_minutes' => 30, 'price' => 50000],
            ['name' => 'Potong + cuci', 'description' => 'Potong dan keramas', 'duration_minutes' => 45, 'price' => 75000],
            ['name' => 'Coloring basic', 'description' => 'Pewarnaan rambut', 'duration_minutes' => 90, 'price' => 150000],
        ];

        foreach ($services as $service) {
            Service::create([
                ...$service,
                'is_active' => true,
                'created_by' => $admin->name,
                'updated_by' => $admin->name,
            ]);
        }

        foreach (['Barber Satu', 'Barber Dua'] as $index => $barberName) {
            $staff = User::create([
                'role' => UserRole::Staff,
                'name' => $barberName,
                'email' => 'barber'.($index + 1).'@digitech-barbershop.test',
                'password' => Hash::make('password'),
                'whatsapp_number' => '+628123456700'.($index + 1),
                'branch_id' => $branch->id,
                'created_by' => $admin->name,
                'updated_by' => $admin->name,
            ]);

            $barber = Barber::create([
                'user_id' => $staff->id,
                'branch_id' => $branch->id,
                'bio' => 'Profesional barber',
                'is_active' => true,
                'created_by' => $admin->name,
                'updated_by' => $admin->name,
            ]);

            foreach (range(1, 6) as $day) {
                BarberSchedule::create([
                    'barber_id' => $barber->id,
                    'day_of_week' => $day,
                    'start_time' => '09:00:00',
                    'end_time' => '21:00:00',
                    'created_by' => $admin->name,
                    'updated_by' => $admin->name,
                ]);
            }
        }
    }
}
