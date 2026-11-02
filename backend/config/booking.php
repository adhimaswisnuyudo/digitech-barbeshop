<?php

return [
    'buffer_minutes' => (int) env('BOOKING_BUFFER_MINUTES', 5),
    'cancel_hours_before' => (int) env('BOOKING_CANCEL_HOURS_BEFORE', 2),
    'reminder_hours' => array_map('intval', explode(',', env('BOOKING_REMINDER_HOURS', '24,2'))),
];
