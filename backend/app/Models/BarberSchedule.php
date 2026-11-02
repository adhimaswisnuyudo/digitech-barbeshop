<?php

namespace App\Models;

class BarberSchedule extends BaseModel
{
    protected $fillable = [
        'barber_id',
        'day_of_week',
        'start_time',
        'end_time',
        'created_by',
        'updated_by',
    ];
}
