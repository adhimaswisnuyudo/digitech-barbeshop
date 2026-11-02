<?php

use App\Http\Controllers\Api\V1\Admin\BarberController as AdminBarberController;
use App\Http\Controllers\Api\V1\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Api\V1\Admin\BranchController as AdminBranchController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AvailabilityController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\BranchController;
use App\Http\Controllers\Api\V1\DeviceController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'data' => [
            'service' => 'digitech-barbershop-api',
            'version' => 'v1',
        ],
    ]);
});

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('google', [AuthController::class, 'google']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::get('branches', [BranchController::class, 'index']);
Route::get('branches/{id}', [BranchController::class, 'show']);
Route::get('services', [ServiceController::class, 'index']);
Route::get('availability', AvailabilityController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);

    Route::post('devices', [DeviceController::class, 'store']);

    Route::get('bookings', [BookingController::class, 'index']);
    Route::post('bookings', [BookingController::class, 'store']);
    Route::get('bookings/{id}', [BookingController::class, 'show']);
    Route::post('bookings/{id}/cancel', [BookingController::class, 'cancel']);
    Route::patch('bookings/{id}/status', [BookingController::class, 'updateStatus']);

    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('dashboard', DashboardController::class);

        Route::get('branches', [AdminBranchController::class, 'index']);
        Route::post('branches', [AdminBranchController::class, 'store']);
        Route::put('branches/{id}', [AdminBranchController::class, 'update']);
        Route::delete('branches/{id}', [AdminBranchController::class, 'destroy']);

        Route::get('services', [AdminServiceController::class, 'index']);
        Route::post('services', [AdminServiceController::class, 'store']);
        Route::put('services/{id}', [AdminServiceController::class, 'update']);
        Route::delete('services/{id}', [AdminServiceController::class, 'destroy']);

        Route::get('barbers', [AdminBarberController::class, 'index']);
        Route::post('barbers', [AdminBarberController::class, 'store']);
        Route::put('barbers/{id}/schedules', [AdminBarberController::class, 'updateSchedules']);
        Route::post('barbers/{id}/time-off', [AdminBarberController::class, 'storeTimeOff']);
        Route::delete('barbers/{barberId}/time-off/{timeOffId}', [AdminBarberController::class, 'destroyTimeOff']);

        Route::post('bookings', [AdminBookingController::class, 'store']);
        Route::patch('bookings/{id}', [AdminBookingController::class, 'update']);
    });
});
