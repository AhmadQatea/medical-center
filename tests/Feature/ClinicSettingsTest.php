<?php

use App\Models\ClinicSetting;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\put;

test('guests cannot view clinic settings', function () {
    get(route('doctor.settings.index'))
        ->assertRedirect(route('login'));
});

test('doctor can view clinic settings page with defaults created', function () {
    $doctor = User::factory()->create();

    actingAs($doctor)
        ->get(route('doctor.settings.index'))
        ->assertOk()
        ->assertSee(config('clinic.name'));

    assertDatabaseHas('clinic_settings', [
        'user_id' => $doctor->id,
        'clinic_name' => config('clinic.name'),
    ]);
});

test('doctor can update clinic settings', function () {
    $doctor = User::factory()->create(['name' => 'د. قديم']);
    ClinicSetting::factory()->for($doctor)->create([
        'clinic_name' => 'عيادة قديمة',
        'whatsapp_number' => '966500000000',
    ]);

    $response = actingAs($doctor)
        ->put(route('doctor.settings.update'), [
            'clinic_name' => 'العيادة السنية التخصصية',
            'doctor_name' => 'العيادة السنية التخصصية',
            'specialty' => 'طبيب أسنان',
            'city' => 'جدة',
            'description' => 'وصف محدث',
            'address' => 'حي الزهراء',
            'whatsapp' => '+966512345678',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('doctor.settings.index'))
        ->assertSessionHas('success');

    $doctor->refresh();

    expect($doctor->name)->toBe('العيادة السنية التخصصية');

    assertDatabaseHas('clinic_settings', [
        'user_id' => $doctor->id,
        'clinic_name' => 'العيادة السنية التخصصية',
        'specialty' => 'طبيب أسنان',
        'city' => 'جدة',
        'description' => 'وصف محدث',
        'address' => 'حي الزهراء',
        'whatsapp_number' => '966512345678',
    ]);
});

test('clinic settings normalizes syrian whatsapp numbers on save', function () {
    $doctor = User::factory()->create();
    ClinicSetting::factory()->for($doctor)->create();

    actingAs($doctor)
        ->put(route('doctor.settings.update'), [
            'clinic_name' => 'العيادة السنية التخصصية',
            'doctor_name' => 'العيادة السنية التخصصية',
            'specialty' => 'طبيب أسنان',
            'whatsapp' => '+963999123456',
        ])
        ->assertSessionHasNoErrors();

    assertDatabaseHas('clinic_settings', [
        'user_id' => $doctor->id,
        'whatsapp_number' => '963999123456',
    ]);
});

test('clinic settings update validates required fields', function () {
    $doctor = User::factory()->create();
    ClinicSetting::factory()->for($doctor)->create();

    actingAs($doctor)
        ->from(route('doctor.settings.index'))
        ->put(route('doctor.settings.update'), [
            'clinic_name' => '',
            'doctor_name' => '',
            'specialty' => '',
            'whatsapp' => '',
        ])
        ->assertSessionHasErrors(['clinic_name', 'doctor_name', 'specialty', 'whatsapp'])
        ->assertRedirect(route('doctor.settings.index'));
});
