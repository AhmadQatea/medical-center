<?php

use App\Models\User;
use App\Services\ClinicSettingsService;
use App\Services\ScheduleService;
use Carbon\Carbon;

use function Pest\Laravel\get;

test('public booking shows empty state when no schedule is configured', function () {
    $doctor = User::factory()->create(['name' => 'د. أحمد']);
    $clinic = $doctor->clinic;
    app(ClinicSettingsService::class)->get($doctor);
    ensureFixedAppointmentTypes($doctor);

    get(route('booking.book', [$clinic, $doctor]))
        ->assertOk()
        ->assertSee('لا توجد مواعيد متاحة')
        ->assertDontSee('هذا الأسبوع')
        ->assertDontSee('اختر التاريخ');
});

test('public booking shows week steps when slots exist', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-25 08:00:00')); // Saturday

    $doctor = User::factory()->create();
    $clinic = $doctor->clinic;
    app(ClinicSettingsService::class)->get($doctor);
    ensureFixedAppointmentTypes($doctor);

    $schedule = app(ScheduleService::class);
    $schedule->getSettings($doctor);
    $schedule->updateSettings($doctor, [
        'appointment_duration_minutes' => 30,
        'break_duration_minutes' => 0,
        'lunch_enabled' => false,
    ]);
    $schedule->syncWorkingHours($doctor, weekdayPayload(openWeekdays: [6]));

    get(route('booking.book', [$clinic, $doctor]))
        ->assertOk()
        ->assertSee('هذا الأسبوع')
        ->assertSee('الأسبوع القادم')
        ->assertSee('اختر الأسبوع')
        ->assertSee('تأكيد الحجز')
        ->assertDontSee('حجز عبر واتساب');
});

test('public booking always provisions fixed appointment types', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-25 08:00:00'));

    $doctor = User::factory()->create();
    $clinic = $doctor->clinic;
    app(ClinicSettingsService::class)->get($doctor);

    $schedule = app(ScheduleService::class);
    $schedule->getSettings($doctor);
    $schedule->updateSettings($doctor, [
        'appointment_duration_minutes' => 30,
        'break_duration_minutes' => 0,
        'lunch_enabled' => false,
    ]);
    $schedule->syncWorkingHours($doctor, weekdayPayload(openWeekdays: [6]));

    get(route('booking.book', [$clinic, $doctor]))
        ->assertOk()
        ->assertSee('معاينة')
        ->assertSee('مراجعة')
        ->assertSee('تأكيد الحجز');
});

/**
 * @param  list<int>  $openWeekdays
 * @return list<array{weekday: int, is_open: bool, start_time: string|null, end_time: string|null}>
 */
function weekdayPayload(array $openWeekdays = []): array
{
    return collect(range(0, 6))
        ->map(fn (int $weekday): array => [
            'weekday' => $weekday,
            'is_open' => in_array($weekday, $openWeekdays, true),
            'start_time' => in_array($weekday, $openWeekdays, true) ? '09:00' : null,
            'end_time' => in_array($weekday, $openWeekdays, true) ? '12:00' : null,
        ])
        ->all();
}
