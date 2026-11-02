<?php

use App\Database\Migration\BlameableColumns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('barber_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained()->cascadeOnDelete();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('status')->default('confirmed');
            $table->string('payment_method')->default('pay_at_shop');
            $table->text('notes')->nullable();
            BlameableColumns::add($table);
        });

        Schema::create('booking_services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('service_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('price_snapshot');
            $table->unsignedSmallInteger('duration_snapshot');
            BlameableColumns::add($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_services');
        Schema::dropIfExists('bookings');
    }
};
