<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarberTimeOff extends BaseModel
{
    protected $table = 'barber_time_off';

    protected $fillable = [
        'barber_id',
        'date',
        'reason',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function barber(): BelongsTo
    {
        return $this->belongsTo(Barber::class);
    }
}
