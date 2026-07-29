<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\Holiday;
use App\Models\User;
use App\Services\ClinicSettingsService;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

test('doctor cannot access another doctors appointment via scoped binding', function () {
    $doctorA = User::factory()->create();
    $doctorB = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctorA);
    app(ClinicSettingsService::class)->get($doctorB);

    $appointment = Appointment::factory()->for($doctorB)->create();

    actingAs($doctorA)
        ->get(route('doctor.bookings.show', $appointment))
        ->assertNotFound();
});

test('doctor cannot access another doctors appointment type', function () {
    $doctorA = User::factory()->create();
    $doctorB = User::factory()->create();

    $type = AppointmentType::factory()->create(['user_id' => $doctorB->id]);

    actingAs($doctorA)
        ->get(route('doctor.appointment-types.edit', $type))
        ->assertNotFound();
});

test('doctor cannot delete another doctors holiday', function () {
    $doctorA = User::factory()->create();
    $doctorB = User::factory()->create();

    $holiday = Holiday::factory()->create(['user_id' => $doctorB->id]);

    actingAs($doctorA)
        ->delete(route('doctor.schedule.holidays.destroy', $holiday))
        ->assertNotFound();
});

test('bookings index rejects invalid status filter', function () {
    $doctor = User::factory()->create();

    actingAs($doctor)
        ->get(route('doctor.bookings.index', ['status' => 'hacked-status']))
        ->assertSessionHasErrors('status');
});

test('cancel booking rejects oversized reason', function () {
    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    $appointment = Appointment::factory()
        ->for($doctor)
        ->create(['status' => AppointmentStatus::Pending]);

    actingAs($doctor)
        ->delete(route('doctor.bookings.destroy', $appointment), [
            'reason' => str_repeat('ا', 300),
        ])
        ->assertSessionHasErrors('reason');
});

test('stub patient mutation routes are not registered', function () {
    $doctor = User::factory()->create();

    actingAs($doctor)
        ->post('/doctor/patients')
        ->assertMethodNotAllowed();

    actingAs($doctor)
        ->patch('/doctor/patients/1')
        ->assertNotFound();
});

test('stub doctor profile mutation routes are not registered', function () {
    $doctor = User::factory()->create();

    actingAs($doctor)
        ->put('/doctor/profile')
        ->assertMethodNotAllowed();

    actingAs($doctor)
        ->delete('/doctor/profile')
        ->assertMethodNotAllowed();
});

test('login route is throttled after repeated failures', function () {
    $doctor = User::factory()->create([
        'email' => 'throttle@test.com',
        'password' => 'correct-password',
    ]);

    for ($i = 0; $i < 5; $i++) {
        post(route('login'), [
            'email' => $doctor->email,
            'password' => 'wrong-password',
        ]);
    }

    post(route('login'), [
        'email' => $doctor->email,
        'password' => 'wrong-password',
    ])->assertStatus(429);
});
