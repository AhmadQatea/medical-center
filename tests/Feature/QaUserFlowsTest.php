<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\Patient;
use App\Models\User;
use App\Services\ClinicSettingsService;
use App\Services\ScheduleService;
use Carbon\Carbon;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

/*
|--------------------------------------------------------------------------
| Doctor Dashboard
|--------------------------------------------------------------------------
*/

test('qa dashboard loads and shows empty states for new clinic', function () {
    $doctor = User::factory()->create(['name' => 'د. QA']);

    actingAs($doctor)
        ->get(route('doctor.dashboard'))
        ->assertOk()
        ->assertSee('لا توجد مواعيد اليوم')
        ->assertSee('لا توجد طلبات معلقة');
});

test('qa dashboard shows real pending and today appointments', function () {
    ['doctor' => $doctor] = qaBookableDoctor('2026-07-29 08:00:00');

    $pending = Appointment::factory()->for($doctor)->create([
        'date' => '2026-07-29',
        'start_time' => '10:00',
        'status' => AppointmentStatus::Pending,
    ]);
    $pending->patient->update(['name' => 'مريض معلق QA']);

    actingAs($doctor)
        ->get(route('doctor.dashboard'))
        ->assertOk()
        ->assertSee('مريض معلق QA')
        ->assertSee('قيد الانتظار');
});

/*
|--------------------------------------------------------------------------
| Public Booking — happy path & validation
|--------------------------------------------------------------------------
*/

test('qa public booking completes happy path', function () {
    ['doctor' => $doctor, 'type' => $type] = qaBookableDoctor('2026-07-29 08:00:00');

    post(route('booking.store'), qaBookingPayload($type, [
        'name' => 'أحمد QA',
        'phone' => '+963959422413',
        'date' => '2026-07-29',
        'start_time' => '09:00',
    ]))->assertRedirect(route('booking.success'));

    assertDatabaseHas('appointments', [
        'user_id' => $doctor->id,
        'status' => AppointmentStatus::Pending->value,
        'appointment_type_id' => $type->id,
    ]);
});

test('qa public booking rejects duplicate slot', function () {
    ['doctor' => $doctor, 'type' => $type] = qaBookableDoctor('2026-07-29 08:00:00');

    Appointment::factory()->for($doctor)->create([
        'date' => '2026-07-29',
        'start_time' => '09:00',
        'status' => AppointmentStatus::Confirmed,
        'appointment_type_id' => $type->id,
    ]);

    post(route('booking.store'), qaBookingPayload($type, [
        'date' => '2026-07-29',
        'start_time' => '09:00',
    ]))->assertSessionHasErrors('start_time');
});

test('qa public booking rejects past date', function () {
    ['type' => $type] = qaBookableDoctor('2026-07-29 08:00:00');

    post(route('booking.store'), qaBookingPayload($type, [
        'date' => '2026-07-28',
        'start_time' => '09:00',
    ]))->assertSessionHasErrors('date');
});

test('qa public booking rejects holiday date', function () {
    ['doctor' => $doctor, 'type' => $type, 'schedule' => $schedule] = qaBookableDoctor('2026-07-29 08:00:00');

    $schedule->createHoliday($doctor, [
        'date' => '2026-07-29',
        'title' => 'إجازة QA',
    ]);

    post(route('booking.store'), qaBookingPayload($type, [
        'date' => '2026-07-29',
        'start_time' => '09:00',
    ]))->assertSessionHasErrors('start_time');
});

test('qa public booking rejects disabled weekday', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-29 08:00:00', 'Asia/Damascus')); // Wednesday

    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    $type = AppointmentType::factory()->create([
        'user_id' => $doctor->id,
        'is_active' => true,
    ]);

    $schedule = app(ScheduleService::class);
    $schedule->getSettings($doctor);
    $schedule->updateSettings($doctor, [
        'appointment_duration_minutes' => 30,
        'break_duration_minutes' => 0,
        'lunch_enabled' => false,
    ]);
    // Only Saturday open; booking on Wednesday
    $schedule->syncWorkingHours($doctor, bookingWeekdayPayload(openWeekdays: [6]));

    post(route('booking.store'), qaBookingPayload($type, [
        'date' => '2026-07-29',
        'start_time' => '09:00',
    ]))->assertSessionHasErrors('start_time');
});

test('qa public booking rejects past time on today', function () {
    ['type' => $type] = qaBookableDoctor('2026-07-29 16:00:00');

    post(route('booking.store'), qaBookingPayload($type, [
        'date' => '2026-07-29',
        'start_time' => '09:00',
    ]))->assertSessionHasErrors('start_time');
});

test('qa public booking rejects invalid phone', function () {
    ['type' => $type] = qaBookableDoctor('2026-07-29 08:00:00');

    post(route('booking.store'), qaBookingPayload($type, [
        'phone' => '12345',
    ]))->assertSessionHasErrors('phone');
});

test('qa public booking rejects empty name', function () {
    ['type' => $type] = qaBookableDoctor('2026-07-29 08:00:00');

    post(route('booking.store'), qaBookingPayload($type, [
        'name' => '',
    ]))->assertSessionHasErrors('name');
});

test('qa public booking rejects inactive appointment type', function () {
    ['doctor' => $doctor] = qaBookableDoctor('2026-07-29 08:00:00');

    $inactiveType = AppointmentType::factory()->create([
        'user_id' => $doctor->id,
        'is_active' => false,
    ]);

    post(route('booking.store'), qaBookingPayload($inactiveType, [
        'date' => '2026-07-29',
        'start_time' => '09:00',
    ]))->assertSessionHasErrors('appointment_type_id');
});

test('qa public booking shows no availability when no working days', function () {
    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);
    AppointmentType::factory()->create(['user_id' => $doctor->id, 'is_active' => true]);
    app(ScheduleService::class)->ensureWorkingHours($doctor);

    get(route('booking.index'))
        ->assertOk()
        ->assertSee('لا توجد مواعيد متاحة');
});

test('qa public booking shows no types empty state', function () {
    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    get(route('booking.index'))
        ->assertOk()
        ->assertSee('لا توجد أنواع مواعيد متاحة حالياً');
});

/*
|--------------------------------------------------------------------------
| Instant Booking
|--------------------------------------------------------------------------
*/

test('qa instant booking page loads with schedule picker', function () {
    ['doctor' => $doctor] = qaBookableDoctor('2026-07-29 08:00:00');

    actingAs($doctor)
        ->get(route('doctor.bookings.instant'))
        ->assertOk()
        ->assertSee('حجز فوري')
        ->assertSee('اختر الأسبوع');
});

test('qa instant booking creates confirmed appointment', function () {
    ['doctor' => $doctor, 'type' => $type] = qaBookableDoctor('2026-07-29 08:00:00');

    actingAs($doctor)
        ->post(route('doctor.bookings.store'), [
            'name' => 'مريض فوري',
            'phone' => '+963959422413',
            'date' => '2026-07-29',
            'start_time' => '10:00',
            'appointment_type_id' => $type->id,
            'status' => AppointmentStatus::Confirmed->value,
        ])
        ->assertRedirect();

    assertDatabaseHas('appointments', [
        'user_id' => $doctor->id,
        'status' => AppointmentStatus::Confirmed->value,
        'start_time' => '10:00',
    ]);
});

test('qa instant booking shows empty state without appointment types', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-29 08:00:00', 'Asia/Damascus'));
    $weekday = Carbon::parse('2026-07-29')->dayOfWeek;

    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    $schedule = app(ScheduleService::class);
    $schedule->getSettings($doctor);
    $schedule->updateSettings($doctor, [
        'appointment_duration_minutes' => 30,
        'break_duration_minutes' => 0,
        'lunch_enabled' => false,
    ]);
    $schedule->syncWorkingHours($doctor, bookingWeekdayPayload(openWeekdays: [$weekday]));

    expect($doctor->appointmentTypes()->count())->toBe(0);

    actingAs($doctor)
        ->get(route('doctor.bookings.instant'))
        ->assertOk()
        ->assertSee('لا توجد أنواع مواعيد متاحة');
});

test('qa instant booking rejects duplicate slot', function () {
    ['doctor' => $doctor, 'type' => $type] = qaBookableDoctor('2026-07-29 08:00:00');

    Appointment::factory()->for($doctor)->create([
        'date' => '2026-07-29',
        'start_time' => '11:00',
        'status' => AppointmentStatus::Pending,
        'appointment_type_id' => $type->id,
    ]);

    actingAs($doctor)
        ->post(route('doctor.bookings.store'), qaBookingPayload($type, [
            'date' => '2026-07-29',
            'start_time' => '11:00',
        ]))
        ->assertSessionHasErrors('start_time');
});

/*
|--------------------------------------------------------------------------
| Schedule Management
|--------------------------------------------------------------------------
*/

test('qa schedule management save and holiday flows work', function () {
    $doctor = User::factory()->create();
    app(ScheduleService::class)->ensureWorkingHours($doctor);

    $days = collect(bookingWeekdayPayload(openWeekdays: [6]))->map(
        fn (array $day, int $i): array => [
            'weekday' => $day['weekday'],
            'is_open' => $day['is_open'] ? '1' : null,
            'start_time' => $day['start_time'] ?? '',
            'end_time' => $day['end_time'] ?? '',
        ],
    )->values()->all();

    actingAs($doctor)
        ->put(route('doctor.schedule.update'), [
            'appointment_duration_minutes' => 30,
            'break_duration_minutes' => 0,
            'lunch_enabled' => null,
            'days' => $days,
        ])
        ->assertRedirect(route('doctor.schedule.index'));

    actingAs($doctor)
        ->post(route('doctor.schedule.holidays.store'), [
            'date' => '2026-08-15',
            'title' => 'QA Holiday',
        ])
        ->assertRedirect(route('doctor.schedule.index'));

    assertDatabaseHas('holidays', [
        'user_id' => $doctor->id,
        'title' => 'QA Holiday',
    ]);
});

/*
|--------------------------------------------------------------------------
| Appointment Types
|--------------------------------------------------------------------------
*/

test('qa appointment types crud and toggle', function () {
    $doctor = User::factory()->create();

    actingAs($doctor)
        ->post(route('doctor.appointment-types.store'), [
            'name' => 'نوع QA',
            'color' => '#6B1E2A',
        ])
        ->assertRedirect(route('doctor.appointment-types.index'));

    $type = AppointmentType::query()->where('user_id', $doctor->id)->firstOrFail();

    actingAs($doctor)
        ->patch(route('doctor.appointment-types.toggle', $type))
        ->assertRedirect(route('doctor.appointment-types.index'));

    expect($type->fresh()->is_active)->toBeFalse();

    actingAs($doctor)
        ->get(route('doctor.appointment-types.index'))
        ->assertOk()
        ->assertSee('نوع QA');
});

/*
|--------------------------------------------------------------------------
| Patients
|--------------------------------------------------------------------------
*/

test('qa patients index lists patients from bookings', function () {
    ['doctor' => $doctor] = qaBookableDoctor('2026-07-29 08:00:00');

    Patient::factory()->create([
        'user_id' => $doctor->id,
        'name' => 'مريض QA',
        'phone' => '963959422413',
    ]);

    actingAs($doctor)
        ->get(route('doctor.patients.index'))
        ->assertOk()
        ->assertSee('مريض QA');
});

/*
|--------------------------------------------------------------------------
| Timeline
|--------------------------------------------------------------------------
*/

test('qa timeline shows today appointments and available slots', function () {
    ['doctor' => $doctor, 'type' => $type] = qaBookableDoctor('2026-07-29 08:00:00');

    $appointment = Appointment::factory()->for($doctor)->create([
        'date' => '2026-07-29',
        'start_time' => '09:00',
        'status' => AppointmentStatus::Confirmed,
        'appointment_type_id' => $type->id,
    ]);
    $appointment->patient->update(['name' => 'مريض الجدول']);

    actingAs($doctor)
        ->get(route('doctor.timeline.index'))
        ->assertOk()
        ->assertSee('مريض الجدول')
        ->assertSee('وقت متاح');
});

test('qa timeline shows empty day when no schedule', function () {
    $doctor = User::factory()->create();

    actingAs($doctor)
        ->get(route('doctor.timeline.index'))
        ->assertOk();
});

/*
|--------------------------------------------------------------------------
| Clinic Settings
|--------------------------------------------------------------------------
*/

test('qa clinic settings page loads and updates', function () {
    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    actingAs($doctor)
        ->get(route('doctor.settings.index'))
        ->assertOk()
        ->assertSee(config('clinic.name'));

    actingAs($doctor)
        ->put(route('doctor.settings.update'), [
            'clinic_name' => 'عيادة QA',
            'doctor_name' => 'د. QA',
            'specialty' => 'طبيب أسنان',
            'whatsapp' => '+963959422413',
        ])
        ->assertRedirect(route('doctor.settings.index'));

    assertDatabaseHas('clinic_settings', [
        'user_id' => $doctor->id,
        'clinic_name' => 'عيادة QA',
    ]);
});

/*
|--------------------------------------------------------------------------
| Booking Details — status actions & edge cases
|--------------------------------------------------------------------------
*/

test('qa booking details page shows full information', function () {
    ['doctor' => $doctor] = qaBookableDoctor('2026-07-29 08:00:00');

    $appointment = Appointment::factory()->for($doctor)->create([
        'status' => AppointmentStatus::Pending,
    ]);

    actingAs($doctor)
        ->get(route('doctor.bookings.show', $appointment))
        ->assertOk()
        ->assertSee('معلومات المريض')
        ->assertSee('معلومات الموعد')
        ->assertSee('تأكيد الحجز');
});

test('qa cannot confirm cancelled booking', function () {
    ['doctor' => $doctor] = qaBookableDoctor('2026-07-29 08:00:00');

    $appointment = Appointment::factory()->for($doctor)->create([
        'status' => AppointmentStatus::Cancelled,
    ]);

    actingAs($doctor)
        ->post(route('doctor.bookings.confirm', $appointment))
        ->assertSessionHasErrors('status');
});

test('qa cannot confirm completed booking', function () {
    ['doctor' => $doctor] = qaBookableDoctor('2026-07-29 08:00:00');

    $appointment = Appointment::factory()->for($doctor)->create([
        'status' => AppointmentStatus::Completed,
    ]);

    actingAs($doctor)
        ->post(route('doctor.bookings.confirm', $appointment))
        ->assertSessionHasErrors('status');
});

test('qa cannot cancel completed booking via destroy', function () {
    ['doctor' => $doctor] = qaBookableDoctor('2026-07-29 08:00:00');

    $appointment = Appointment::factory()->for($doctor)->create([
        'status' => AppointmentStatus::Completed,
    ]);

    actingAs($doctor)
        ->delete(route('doctor.bookings.destroy', $appointment))
        ->assertSessionHasErrors('status');
});

test('qa cannot reschedule cancelled booking', function () {
    ['doctor' => $doctor, 'type' => $type] = qaBookableDoctor('2026-07-29 08:00:00');

    $appointment = Appointment::factory()->for($doctor)->create([
        'date' => '2026-07-29',
        'start_time' => '12:00',
        'status' => AppointmentStatus::Cancelled,
        'appointment_type_id' => $type->id,
    ]);

    actingAs($doctor)
        ->patch(route('doctor.bookings.update', $appointment), [
            'date' => '2026-07-29',
            'start_time' => '13:00',
        ])
        ->assertForbidden();
});

test('qa cannot edit completed booking', function () {
    ['doctor' => $doctor] = qaBookableDoctor('2026-07-29 08:00:00');

    $appointment = Appointment::factory()->for($doctor)->create([
        'status' => AppointmentStatus::Completed,
    ]);

    actingAs($doctor)
        ->get(route('doctor.bookings.edit', $appointment))
        ->assertForbidden();
});

test('qa doctor can reschedule pending booking to free slot', function () {
    ['doctor' => $doctor, 'type' => $type] = qaBookableDoctor('2026-07-29 08:00:00');

    $appointment = Appointment::factory()->for($doctor)->create([
        'date' => '2026-07-29',
        'start_time' => '12:00',
        'status' => AppointmentStatus::Pending,
        'appointment_type_id' => $type->id,
    ]);

    actingAs($doctor)
        ->patch(route('doctor.bookings.update', $appointment), [
            'date' => '2026-07-29',
            'start_time' => '13:00',
        ])
        ->assertRedirect(route('doctor.bookings.show', $appointment));

    expect(\App\Support\TimeFormat::normalize((string) $appointment->fresh()->start_time))->toBe('13:00');
});

test('qa bookings list shows appointments and filters by status', function () {
    ['doctor' => $doctor] = qaBookableDoctor('2026-07-29 08:00:00');

    $confirmed = Appointment::factory()->for($doctor)->create(['status' => AppointmentStatus::Confirmed]);
    $pending = Appointment::factory()->for($doctor)->create(['status' => AppointmentStatus::Pending]);
    $confirmed->patient->update(['name' => 'مؤكد QA']);
    $pending->patient->update(['name' => 'معلق QA']);

    actingAs($doctor)
        ->get(route('doctor.bookings.index'))
        ->assertOk()
        ->assertSee('مؤكد QA')
        ->assertSee('معلق QA');

    actingAs($doctor)
        ->get(route('doctor.bookings.index', ['status' => AppointmentStatus::Confirmed->value]))
        ->assertOk()
        ->assertSee('مؤكد QA')
        ->assertDontSee('معلق QA');
});

/**
 * @return array{
 *     doctor: User,
 *     type: AppointmentType,
 *     schedule: ScheduleService,
 *     weekday: int
 * }
 */
function qaBookableDoctor(string $now, ?User $doctor = null): array
{
    Carbon::setTestNow(Carbon::parse($now, 'Asia/Damascus'));
    $weekday = Carbon::parse(substr($now, 0, 10), 'Asia/Damascus')->dayOfWeek;

    $doctor ??= User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    $type = AppointmentType::factory()->create([
        'user_id' => $doctor->id,
        'is_active' => true,
    ]);

    $schedule = app(ScheduleService::class);
    $schedule->getSettings($doctor);
    $schedule->updateSettings($doctor, [
        'appointment_duration_minutes' => 30,
        'break_duration_minutes' => 0,
        'lunch_enabled' => false,
    ]);
    $schedule->syncWorkingHours($doctor, bookingWeekdayPayload(openWeekdays: [$weekday]));

    return compact('doctor', 'type', 'schedule', 'weekday');
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function qaBookingPayload(AppointmentType $type, array $overrides = []): array
{
    return array_merge([
        'name' => 'مريض QA',
        'phone' => '+963959422413',
        'date' => '2026-07-29',
        'start_time' => '09:00',
        'appointment_type_id' => $type->id,
    ], $overrides);
}
