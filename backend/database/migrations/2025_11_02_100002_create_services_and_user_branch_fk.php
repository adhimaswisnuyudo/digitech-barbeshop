<?php

use App\Database\Migration\BlameableColumns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('duration_minutes');
            $table->unsignedInteger('price');
            $table->boolean('is_active')->default(true);
            BlameableColumns::add($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
        });
    }
};
