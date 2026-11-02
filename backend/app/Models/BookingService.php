<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingService extends BaseModel
{
    protected $fillable = [
        'booking_id',
        'service_id',
        'price_snapshot',
        'duration_snapshot',
        'created_by',
        'updated_by',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
