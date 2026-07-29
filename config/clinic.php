<?php

/**
 * Clinic identity — Specialized Dental Clinic
 */

return [

    'name' => 'العيادة السنية التخصصية',

    'name_en' => 'Specialized Dental Clinic',

    'doctor' => [
        'name' => 'العيادة السنية التخصصية',
        'name_en' => 'Specialized Dental Clinic',
        'title' => 'طبيب أسنان',
        'specialty' => 'طبيب أسنان',
        'initials' => 'عس',
    ],

    'description' => 'ابتسامة أجمل تبدأ بثقة ورعاية احترافية.',

    'city' => null,

    'address' => null,

    'whatsapp' => '963999123456',

    'email' => 'clinic@example.com',

    /*
    |--------------------------------------------------------------------------
    | Clinic timezone
    |--------------------------------------------------------------------------
    |
    | Used for slot availability, "today" boundaries, and displayed times.
    | Syrian clinics should use Asia/Damascus.
    |
    */
    'timezone' => env('CLINIC_TIMEZONE', 'Asia/Damascus'),

    /*
    |--------------------------------------------------------------------------
    | Seeded doctor account (local/testing only)
    |--------------------------------------------------------------------------
    */

    'seed_doctor' => [
        'email' => env('DOCTOR_EMAIL', 'clinic@example.com'),
        'name' => env('DOCTOR_NAME', 'العيادة السنية التخصصية'),
        'password' => env('DOCTOR_PASSWORD'),
    ],

];
