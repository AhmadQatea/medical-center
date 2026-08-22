<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use App\Services\ClinicSettingsService;
use App\Services\ScheduleService;
use Carbon\Carbon;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

test('booking success shows whatsapp link to medical center with booking details', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-29 08:00:00'));
    $weekday = Carbon::parse('2026-07-29')->dayOfWeek;

    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    $type = ensureFixedAppointmentTypes($doctor)->firstWhere('name', 'معاينة');

    $schedule = app(ScheduleService::class);
    $schedule->getSettings($doctor);
    $schedule->updateSettings($doctor, [
        'appointment_duration_minutes' => 30,
        'break_duration_minutes' => 0,
        'lunch_enabled' => false,
    ]);
    $schedule->syncWorkingHours($doctor, bookingWeekdayPayload(openWeekdays: [$weekday]));

    post(route('booking.store'), publicBookingPayload($doctor, $type, [
        'name' => 'أحمد علي',
        'phone' => '+963999123456',
    ]))->assertRedirect(route('booking.success'));

    get(route('booking.success'))
        ->assertOk()
        ->assertSee('إرسال الحجز عبر واتساب')
        ->assertSee('أحمد علي')
        ->assertSee('wa.me/'.config('clinic.medical_center.whatsapp'), false);
});

test('public booking stores pending appointment in database', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-29 08:00:00'));
    $weekday = Carbon::parse('2026-07-29')->dayOfWeek;

    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    $type = ensureFixedAppointmentTypes($doctor)->firstWhere('name', 'معاينة');

    $schedule = app(ScheduleService::class);
    $schedule->getSettings($doctor);
    $schedule->updateSettings($doctor, [
        'appointment_duration_minutes' => 30,
        'break_duration_minutes' => 0,
        'lunch_enabled' => false,
    ]);
    $schedule->syncWorkingHours($doctor, bookingWeekdayPayload(openWeekdays: [$weekday]));

    post(route('booking.store'), publicBookingPayload($doctor, $type, [
        'name' => 'أحمد علي',
        'phone' => '+963999123456',
    ]))
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

    $appointment = Appointment::factory()
        ->for($doctor)
        ->create(['status' => AppointmentStatus::Pending]);

    actingAs($doctor)
        ->post(route('doctor.bookings.confirm', $appointment))
        ->assertRedirect(route('doctor.bookings.show', $appointment));

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Confirmed);
});

test('doctor gets fixed appointment types automatically', function () {
    $doctor = User::factory()->create();

    $types = ensureFixedAppointmentTypes($doctor);

    expect($types)->toHaveCount(2);
    expect($types->pluck('name')->all())->toBe(['معاينة', 'مراجعة']);
});

test('doctor bookings index lists appointments and filters by status', function () {
    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    $confirmed = Appointment::factory()
        ->for($doctor)
        ->create([
            'status' => AppointmentStatus::Confirmed,
            'date' => '2026-09-01',
            'start_time' => '10:00:00',
        ]);

    $pending = Appointment::factory()
        ->for($doctor)
        ->create([
            'status' => AppointmentStatus::Pending,
            'date' => '2026-09-02',
            'start_time' => '11:00:00',
        ]);

    $confirmed->patient->update(['name' => 'مريض مؤكد']);
    $pending->patient->update(['name' => 'مريض معلق']);

    actingAs($doctor)
        ->get(route('doctor.bookings.index'))
        ->assertOk()
        ->assertSee('مريض مؤكد')
        ->assertSee('مريض معلق')
        ->assertSee('من أصل');

    actingAs($doctor)
        ->get(route('doctor.bookings.index', ['status' => AppointmentStatus::Confirmed->value]))
        ->assertOk()
        ->assertSee('مريض مؤكد')
        ->assertDontSee('مريض معلق');
});
