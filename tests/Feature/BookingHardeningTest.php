<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use App\Services\BookingService;
use App\Services\ClinicSettingsService;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

test('duplicate booking for same slot is rejected', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-29 08:00:00', 'Asia/Damascus'));
    $weekday = Carbon::parse('2026-07-29')->dayOfWeek;

    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    $type = ensureFixedAppointmentTypes($doctor)->first();

    $schedule = app(ScheduleService::class);
    $schedule->getSettings($doctor);
    $schedule->updateSettings($doctor, [
        'appointment_duration_minutes' => 30,
        'break_duration_minutes' => 0,
        'lunch_enabled' => false,
    ]);
    $schedule->syncWorkingHours($doctor, bookingWeekdayPayload(openWeekdays: [$weekday]));

    Appointment::factory()->for($doctor)->create([
        'date' => '2026-07-29',
        'start_time' => '09:00',
        'end_time' => '09:30:00',
        'status' => AppointmentStatus::Confirmed,
        'appointment_type_id' => $type->id,
    ]);

    post(route('booking.store'), publicBookingPayload($doctor, $type, [
        'name' => 'مريض ثاني',
        'phone' => '+963959422414',
    ]))->assertSessionHasErrors('start_time');

    expect(Appointment::query()->whereDate('date', '2026-07-29')->where('start_time', '09:00')->count())->toBe(1);
});

test('completed appointment does not block the same slot', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-29 08:00:00', 'Asia/Damascus'));
    $weekday = Carbon::parse('2026-07-29')->dayOfWeek;

    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    $type = ensureFixedAppointmentTypes($doctor)->first();

    $schedule = app(ScheduleService::class);
    $schedule->getSettings($doctor);
    $schedule->updateSettings($doctor, [
        'appointment_duration_minutes' => 30,
        'break_duration_minutes' => 0,
        'lunch_enabled' => false,
    ]);
    $schedule->syncWorkingHours($doctor, bookingWeekdayPayload(openWeekdays: [$weekday]));

    Appointment::factory()->for($doctor)->create([
        'date' => '2026-07-29',
        'start_time' => '10:00',
        'end_time' => '10:30:00',
        'status' => AppointmentStatus::Completed,
        'appointment_type_id' => $type->id,
    ]);

    post(route('booking.store'), publicBookingPayload($doctor, $type, [
        'name' => 'مريض جديد',
        'phone' => '+963959422415',
        'start_time' => '10:00',
    ]))->assertRedirect(route('booking.success'));

    expect(Appointment::query()
        ->whereDate('date', '2026-07-29')
        ->where('start_time', '10:00')
        ->where('status', AppointmentStatus::Pending)
        ->exists())->toBeTrue();
});

test('booking service rejects duplicate slot on create', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-29 08:00:00', 'Asia/Damascus'));
    $weekday = Carbon::parse('2026-07-29')->dayOfWeek;

    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    $type = ensureFixedAppointmentTypes($doctor)->first();

    $schedule = app(ScheduleService::class);
    $schedule->getSettings($doctor);
    $schedule->updateSettings($doctor, [
        'appointment_duration_minutes' => 30,
        'break_duration_minutes' => 0,
        'lunch_enabled' => false,
    ]);
    $schedule->syncWorkingHours($doctor, bookingWeekdayPayload(openWeekdays: [$weekday]));

    Appointment::factory()->for($doctor)->create([
        'date' => '2026-07-29',
        'start_time' => '11:00',
        'end_time' => '11:30:00',
        'status' => AppointmentStatus::Pending,
        'appointment_type_id' => $type->id,
    ]);

    $bookings = app(BookingService::class);

    expect(fn () => $bookings->createPublic($doctor, [
        'name' => 'اختبار',
        'phone' => '+963959422416',
        'date' => '2026-07-29',
        'start_time' => '11:00',
        'appointment_type_id' => $type->id,
    ]))->toThrow(ValidationException::class);
});

test('cannot confirm cancelled appointment', function () {
    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    $appointment = Appointment::factory()
        ->for($doctor)
        ->create(['status' => AppointmentStatus::Cancelled]);

    actingAs($doctor)
        ->post(route('doctor.bookings.confirm', $appointment))
        ->assertSessionHasErrors('status');
});

test('cannot confirm completed appointment', function () {
    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    $appointment = Appointment::factory()
        ->for($doctor)
        ->create(['status' => AppointmentStatus::Completed]);

    actingAs($doctor)
        ->post(route('doctor.bookings.confirm', $appointment))
        ->assertSessionHasErrors('status');
});

test('cannot cancel completed appointment', function () {
    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    $appointment = Appointment::factory()
        ->for($doctor)
        ->create(['status' => AppointmentStatus::Completed]);

    actingAs($doctor)
        ->patch(route('doctor.bookings.status', $appointment), [
            'status' => AppointmentStatus::Cancelled->value,
        ])
        ->assertSessionHasErrors('status');
});

test('cannot edit completed appointment', function () {
    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    $appointment = Appointment::factory()
        ->for($doctor)
        ->create(['status' => AppointmentStatus::Completed]);

    actingAs($doctor)
        ->get(route('doctor.bookings.edit', $appointment))
        ->assertForbidden();
});

test('reschedule rejects taken slot', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-29 08:00:00', 'Asia/Damascus'));
    $weekday = Carbon::parse('2026-07-29')->dayOfWeek;

    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    $type = ensureFixedAppointmentTypes($doctor)->first();

    $schedule = app(ScheduleService::class);
    $schedule->getSettings($doctor);
    $schedule->updateSettings($doctor, [
        'appointment_duration_minutes' => 30,
        'break_duration_minutes' => 0,
        'lunch_enabled' => false,
    ]);
    $schedule->syncWorkingHours($doctor, bookingWeekdayPayload(openWeekdays: [$weekday]));

    Appointment::factory()->for($doctor)->create([
        'date' => '2026-07-29',
        'start_time' => '14:00',
        'end_time' => '14:30:00',
        'status' => AppointmentStatus::Confirmed,
        'appointment_type_id' => $type->id,
    ]);

    $pending = Appointment::factory()->for($doctor)->create([
        'date' => '2026-07-29',
        'start_time' => '15:00',
        'end_time' => '15:30:00',
        'status' => AppointmentStatus::Pending,
        'appointment_type_id' => $type->id,
    ]);

    actingAs($doctor)
        ->patch(route('doctor.bookings.update', $pending), [
            'date' => '2026-07-29',
            'start_time' => '14:00',
        ])
        ->assertSessionHasErrors('start_time');
});

test('public booking rejects past date', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-29 10:00:00', 'Asia/Damascus'));

    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    $type = ensureFixedAppointmentTypes($doctor)->first();

    post(route('booking.store'), publicBookingPayload($doctor, $type, [
        'name' => 'مريض',
        'phone' => '+963959422417',
        'date' => '2026-07-28',
    ]))->assertSessionHasErrors('date');
});

test('active appointment sets slot guard key', function () {
    $appointment = Appointment::factory()->create([
        'status' => AppointmentStatus::Confirmed,
        'date' => '2026-08-01',
        'start_time' => '09:00',
    ]);

    expect($appointment->slot_guard_key)->toBe('2026-08-01|09:00');
});

test('cancelled appointment clears slot guard key', function () {
    $appointment = Appointment::factory()->create([
        'status' => AppointmentStatus::Confirmed,
        'date' => '2026-08-01',
        'start_time' => '09:00',
    ]);

    $appointment->status = AppointmentStatus::Cancelled;
    $appointment->save();

    expect($appointment->fresh()->slot_guard_key)->toBeNull();
});
