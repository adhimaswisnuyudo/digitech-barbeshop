<?php

use App\Database\Migration\BlameableColumns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('address');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->json('opening_hours');
            $table->string('timezone')->default('Asia/Jakarta');
            $table->boolean('is_active')->default(true);
            BlameableColumns::add($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
