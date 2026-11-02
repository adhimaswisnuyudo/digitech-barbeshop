<?php

use App\Database\Migration\BlameableColumns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barbers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained()->cascadeOnDelete();
            $table->text('bio')->nullable();
            $table->boolean('is_active')->default(true);
            BlameableColumns::add($table);
        });

        Schema::create('barber_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('barber_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            BlameableColumns::add($table);
        });

        Schema::create('barber_time_off', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('barber_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('reason')->nullable();
            BlameableColumns::add($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barber_time_off');
        Schema::dropIfExists('barber_schedules');
        Schema::dropIfExists('barbers');
    }
};
