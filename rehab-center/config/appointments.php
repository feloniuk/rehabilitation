<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Appointment Slot Interval (in minutes)
    |--------------------------------------------------------------------------
    |
    | Determines the time interval between available appointment slots.
    | For example, 60 means slots at 11:00, 12:00, 13:00, etc.
    | 30 means slots at 11:00, 11:30, 12:00, 12:30, etc.
    |
    */
    'slot_interval' => (int) env('APPOINTMENT_SLOT_INTERVAL', 60),
];
