<?php

namespace App\Models;

class Service extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
        'duration_minutes',
        'price',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
