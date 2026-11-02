<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends BaseModel
{
    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'opening_hours',
        'timezone',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'opening_hours' => 'array',
            'is_active' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function barbers(): HasMany
    {
        return $this->hasMany(Barber::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
