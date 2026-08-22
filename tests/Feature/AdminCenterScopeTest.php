<?php

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use App\Services\ClinicSettingsService;
use App\Services\ScheduleService;
use App\Support\TimeFormat;
use Carbon\Carbon;

use function Pest\Laravel\actingAs;

test('admin patients index lists patients from all doctors', function () {
    $admin = User::factory()->admin()->create();
    $clinic = Clinic::factory()->create(['is_active' => true]);
    $doctor = User::factory()->create([
        'clinic_id' => $clinic->id,
        'role' => UserRole::Doctor,
        'is_active' => true,
    ]);

    $patient = Patient::factory()->create([
        'user_id' => $doctor->id,
        'name' => 'مريض المركز',
        'phone' => '963999111222',
    ]);

    actingAs($admin)
        ->get(route('doctor.patients.index'))
        ->assertOk()
        ->assertSee('مريض المركز')
        ->assertSee($doctor->name);
});

test('admin timeline requires clinic and doctor then shows that doctor day', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-29 08:00:00'));

    $admin = User::factory()->admin()->create();
    $clinic = Clinic::factory()->create(['is_active' => true]);
    $doctor = User::factory()->create([
        'clinic_id' => $clinic->id,
        'role' => UserRole::Doctor,
        'is_active' => true,
        'name' => 'د. خط زمني',
    ]);

    app(ClinicSettingsService::class)->get($doctor);
    $schedule = app(ScheduleService::class);
    $schedule->getSettings($doctor);
    $schedule->updateSettings($doctor, [
        'appointment_duration_minutes' => 30,
        'break_duration_minutes' => 0,
        'lunch_enabled' => false,
    ]);
    $schedule->syncWorkingHours($doctor, bookingWeekdayPayload(openWeekdays: [3]));

    $appointment = Appointment::factory()->for($doctor)->create([
        'clinic_id' => $clinic->id,
        'date' => '2026-07-29',
        'start_time' => '10:00:00',
        'status' => AppointmentStatus::Pending,
    ]);
    $appointment->patient->update(['name' => 'مريض الخط الزمني']);

    actingAs($admin)
        ->get(route('doctor.timeline.index'))
        ->assertOk()
        ->assertSee('اختر العيادة');

    actingAs($admin)
        ->get(route('doctor.timeline.index', [
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id,
        ]))
        ->assertOk()
        ->assertSee('مريض الخط الزمني');
});

test('admin can reschedule another doctor appointment', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-29 08:00:00'));

    $admin = User::factory()->admin()->create();
    $clinic = Clinic::factory()->create(['is_active' => true]);
    $doctor = User::factory()->create([
        'clinic_id' => $clinic->id,
        'role' => UserRole::Doctor,
        'is_active' => true,
    ]);

    app(ClinicSettingsService::class)->get($doctor);
    $schedule = app(ScheduleService::class);
    $schedule->getSettings($doctor);
    $schedule->updateSettings($doctor, [
        'appointment_duration_minutes' => 30,
        'break_duration_minutes' => 0,
        'lunch_enabled' => false,
    ]);
    $schedule->syncWorkingHours($doctor, bookingWeekdayPayload(openWeekdays: [3]));

    $appointment = Appointment::factory()->for($doctor)->create([
        'clinic_id' => $clinic->id,
        'date' => '2026-07-29',
        'start_time' => '09:00:00',
        'status' => AppointmentStatus::Pending,
    ]);

    actingAs($admin)
        ->patch(route('doctor.bookings.update', $appointment), [
            'date' => '2026-07-29',
            'start_time' => '10:00',
        ])
        ->assertRedirect(route('doctor.bookings.show', $appointment));

    expect(TimeFormat::normalize((string) $appointment->fresh()->start_time))->toBe('10:00');
});
