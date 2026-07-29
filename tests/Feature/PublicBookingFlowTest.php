<?php

use App\Models\User;
use App\Services\ClinicSettingsService;
use App\Services\ScheduleService;
use Carbon\Carbon;

use function Pest\Laravel\get;

test('public booking shows empty state when no schedule is configured', function () {
    $doctor = User::factory()->create(['name' => 'العيادة السنية التخصصية']);
    app(ClinicSettingsService::class)->get($doctor);
    \App\Models\AppointmentType::factory()->create([
        'user_id' => $doctor->id,
        'name' => 'معاينة',
        'is_active' => true,
    ]);

    get(route('booking.index'))
        ->assertOk()
        ->assertSee('لا توجد مواعيد متاحة')
        ->assertDontSee('هذا الأسبوع')
        ->assertDontSee('اختر التاريخ');
});

test('public booking shows week steps when slots exist', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-25 08:00:00')); // Saturday

    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);
    \App\Models\AppointmentType::factory()->create([
        'user_id' => $doctor->id,
        'name' => 'معاينة',
        'is_active' => true,
    ]);

    $schedule = app(ScheduleService::class);
    $schedule->getSettings($doctor);
    $schedule->updateSettings($doctor, [
        'appointment_duration_minutes' => 30,
        'break_duration_minutes' => 0,
        'lunch_enabled' => false,
    ]);
    $schedule->syncWorkingHours($doctor, weekdayPayload(openWeekdays: [6]));

    get(route('booking.index'))
        ->assertOk()
        ->assertSee('هذا الأسبوع')
        ->assertSee('الأسبوع القادم')
        ->assertSee('اختر الأسبوع')
        ->assertSee('تأكيد الحجز')
        ->assertDontSee('حجز عبر واتساب');
});

test('public booking hides form when no appointment types exist', function () {
    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    get(route('booking.index'))
        ->assertOk()
        ->assertSee('لا توجد أنواع مواعيد متاحة حالياً')
        ->assertDontSee('تأكيد الحجز');
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
