<?php

namespace App\Database\Migration;

use Illuminate\Database\Schema\Blueprint;

final class BlameableColumns
{
    public static function add(Blueprint $table): void
    {
        $table->timestamps();
        $table->string('created_by');
        $table->string('updated_by');
    }
}
