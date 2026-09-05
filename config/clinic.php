<?php

/**
 * Medical center identity, branding, and default department seed values.
 *
 * Org-level identity: medical_center.* and brand.*
 * Department defaults (seeding only): default_department.*
 */

return [

    'medical_center' => [
        'name' => env('MEDICAL_CENTER_NAME', 'المركز الطبي التخصصي'),
        'name_en' => env('MEDICAL_CENTER_NAME_EN', 'Specialized Medical Center'),
        'description' => env('MEDICAL_CENTER_DESCRIPTION', 'مركز طبي متكامل يضم عدة عيادات وتخصصات.'),
        'whatsapp' => env('MEDICAL_CENTER_WHATSAPP', env('CLINIC_WHATSAPP', '0959422413')),
        'email' => env('MEDICAL_CENTER_EMAIL', env('DOCTOR_EMAIL', 'clinic@example.com')),
        'address' => env('MEDICAL_CENTER_ADDRESS'),
        'city' => env('MEDICAL_CENTER_CITY'),
    ],

    'brand' => [
        'name' => env('APP_BRAND_NAME', 'CarePoint'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default department (first seeded clinic — not the org identity)
    |--------------------------------------------------------------------------
    */
    'default_department' => [
        'name' => env('DEFAULT_CLINIC_NAME', 'عيادة الأسنان'),
        'name_en' => env('DEFAULT_CLINIC_NAME_EN', 'Dental Clinic'),
        'slug' => env('DEFAULT_CLINIC_SLUG', 'dental'),
        'specialty' => env('DEFAULT_CLINIC_SPECIALTY', 'طبيب أسنان'),
        'description' => env('DEFAULT_CLINIC_DESCRIPTION', 'رعاية أسنان متخصصة ضمن المركز الطبي.'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy keys — prefer medical_center.* / default_department.* in new code
    |--------------------------------------------------------------------------
    */
    'name' => env('DEFAULT_CLINIC_NAME', 'عيادة الأسنان'),
    'name_en' => env('DEFAULT_CLINIC_NAME_EN', 'Dental Clinic'),
    'description' => env('DEFAULT_CLINIC_DESCRIPTION', 'رعاية أسنان متخصصة ضمن المركز الطبي.'),
    'city' => env('MEDICAL_CENTER_CITY'),
    'address' => env('MEDICAL_CENTER_ADDRESS'),
    'whatsapp' => env('MEDICAL_CENTER_WHATSAPP', env('CLINIC_WHATSAPP', '0959422413')),
    'email' => env('MEDICAL_CENTER_EMAIL', env('DOCTOR_EMAIL', 'clinic@example.com')),

    'doctor' => [
        'specialty' => env('DEFAULT_CLINIC_SPECIALTY', 'طبيب أسنان'),
    ],

    'timezone' => env('CLINIC_TIMEZONE', 'Asia/Damascus'),

    /*
    |--------------------------------------------------------------------------
    | Seeded admin account (local/testing only)
    |--------------------------------------------------------------------------
    */
    'seed_doctor' => [
        'email' => env('DOCTOR_EMAIL', 'clinic@example.com'),
        'name' => env('ADMIN_NAME', 'مدير المركز'),
        // Override in production with DOCTOR_PASSWORD when possible.
        'password' => env('DOCTOR_PASSWORD', 'admin123123'),
    ],

];
