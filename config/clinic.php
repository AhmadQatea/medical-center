<?php

/**
 * Clinic identity — Dr. Mustafa Bakro Dental Clinic
 */

return [

    'name' => 'عيادة الدكتور مصطفى بكرو',

    'name_en' => 'Dr. Mustafa Bakro Dental Clinic',

    'doctor' => [
        'name' => 'د. مصطفى بكرو',
        'name_en' => 'Dr. Mustafa Bakro',
        'title' => 'طبيب أسنان',
        'specialty' => 'طبيب أسنان',
        'initials' => 'مب',
    ],

    'description' => 'ابتسامة أجمل تبدأ بثقة ورعاية احترافية.',

    'city' => 'الرياض',

    'address' => 'حي الياسمين، الرياض',

    'whatsapp' => '963959422413',

    'email' => 'admin@gmail.com',

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
        'email' => env('DOCTOR_EMAIL', 'admin@gmail.com'),
        'name' => env('DOCTOR_NAME', 'د. مصطفى بكرو'),
        'password' => env('DOCTOR_PASSWORD'),
    ],

];
