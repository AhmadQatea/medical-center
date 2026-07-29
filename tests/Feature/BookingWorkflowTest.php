<?php

use App\Enums\AppointmentStatus;
use App\Models\AppointmentType;
use App\Models\User;
use App\Services\ClinicSettingsService;
use App\Services\ScheduleService;
use Carbon\Carbon;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

test('public booking stores pending appointment in database', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-29 08:00:00'));
    $weekday = Carbon::parse('2026-07-29')->dayOfWeek;

    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    $type = AppointmentType::factory()->create([
        'user_id' => $doctor->id,
        'name' => 'استشارة',
        'is_active' => true,
        'display_order' => 1,
    ]);

    $schedule = app(ScheduleService::class);
    $schedule->getSettings($doctor);
    $schedule->updateSettings($doctor, [
        'appointment_duration_minutes' => 30,
        'break_duration_minutes' => 0,
        'lunch_enabled' => false,
    ]);
    $schedule->syncWorkingHours($doctor, bookingWeekdayPayload(openWeekdays: [$weekday]));

    post(route('booking.store'), [
        'name' => 'أحمد علي',
        'phone' => '+963999123456',
        'date' => '2026-07-29',
        'start_time' => '09:00',
        'appointment_type_id' => $type->id,
    ])
        ->assertRedirect(route('booking.success'));

    assertDatabaseHas('appointments', [
        'user_id' => $doctor->id,
        'status' => AppointmentStatus::Pending->value,
        'appointment_type_id' => $type->id,
    ]);

    assertDatabaseHas('patients', [
        'user_id' => $doctor->id,
        'name' => 'أحمد علي',
    ]);
});

test('doctor can confirm pending booking', function () {
    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    $appointment = \App\Models\Appointment::factory()
        ->for($doctor)
        ->create(['status' => AppointmentStatus::Pending]);

    actingAs($doctor)
        ->post(route('doctor.bookings.confirm', $appointment))
        ->assertRedirect(route('doctor.bookings.show', $appointment));

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Confirmed);
});

test('doctor can manage appointment types', function () {
    $doctor = User::factory()->create();

    actingAs($doctor)
        ->post(route('doctor.appointment-types.store'), [
            'name' => 'معاينة',
            'color' => '#6B1E2A',
        ])
        ->assertRedirect(route('doctor.appointment-types.index'));

    assertDatabaseHas('appointment_types', [
        'user_id' => $doctor->id,
        'name' => 'معاينة',
        'is_active' => true,
    ]);
});

test('doctor bookings index lists appointments and filters by status', function () {
    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    $confirmed = \App\Models\Appointment::factory()
        ->for($doctor)
        ->create(['status' => AppointmentStatus::Confirmed]);

    $pending = \App\Models\Appointment::factory()
        ->for($doctor)
        ->create(['status' => AppointmentStatus::Pending]);

    $confirmed->patient->update(['name' => 'مريض مؤكد']);
    $pending->patient->update(['name' => 'مريض معلق']);

    actingAs($doctor)
        ->get(route('doctor.bookings.index'))
        ->assertOk()
        ->assertSee('مريض مؤكد')
        ->assertSee('مريض معلق')
        ->assertSee('عدد النتائج');

    actingAs($doctor)
        ->get(route('doctor.bookings.index', ['status' => AppointmentStatus::Confirmed->value]))
        ->assertOk()
        ->assertSee('مريض مؤكد')
        ->assertDontSee('مريض معلق');
});
