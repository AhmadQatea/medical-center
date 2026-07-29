<?php

use App\Models\User;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-07-25 08:00:00')); // Saturday morning
});

afterEach(function () {
    Carbon::setTestNow();
});

test('available slots respect duration lunch and closed weekdays', function () {
    $doctor = User::factory()->create();
    $schedule = app(ScheduleService::class);

    $schedule->updateSettings($doctor, [
        'appointment_duration_minutes' => 30,
        'break_duration_minutes' => 0,
        'lunch_enabled' => true,
        'lunch_start' => '10:00',
        'lunch_end' => '11:00',
    ]);

    $schedule->syncWorkingHours($doctor, collect(range(0, 6))->map(
        fn (int $weekday): array => [
            'weekday' => $weekday,
            'is_open' => $weekday === 6,
            'start_time' => $weekday === 6 ? '09:00' : null,
            'end_time' => $weekday === 6 ? '12:00' : null,
        ],
    )->all());

    $saturday = Carbon::parse('2026-07-25');
    $sunday = Carbon::parse('2026-07-26');

    expect($schedule->availableSlots($doctor, $saturday)->all())
        ->toBe(['09:00', '09:30', '11:00', '11:30'])
        ->and($schedule->availableSlots($doctor, $sunday)->all())
        ->toBe([]);
});

test('available slots hide past times on today', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-29 15:57:00', 'Asia/Damascus'));
    $weekday = Carbon::parse('2026-07-29', 'Asia/Damascus')->dayOfWeek;

    $doctor = User::factory()->create();
    $schedule = app(ScheduleService::class);

    $schedule->updateSettings($doctor, [
        'appointment_duration_minutes' => 20,
        'break_duration_minutes' => 0,
        'lunch_enabled' => false,
    ]);

    $schedule->syncWorkingHours($doctor, collect(range(0, 6))->map(
        fn (int $day): array => [
            'weekday' => $day,
            'is_open' => $day === $weekday,
            'start_time' => $day === $weekday ? '09:00' : null,
            'end_time' => $day === $weekday ? '17:00' : null,
        ],
    )->all());

    $slots = $schedule->availableSlots($doctor, Carbon::parse('2026-07-29', 'Asia/Damascus'))->all();

    expect($slots)->not->toContain('14:00', '14:20', '14:40', '15:00')
        ->and($slots)->toContain('16:00');
});

test('booking weeks only include days with labeled time slots', function () {
    $doctor = User::factory()->create();
    $schedule = app(ScheduleService::class);

    $schedule->updateSettings($doctor, [
        'appointment_duration_minutes' => 60,
        'break_duration_minutes' => 0,
        'lunch_enabled' => false,
    ]);

    $schedule->syncWorkingHours($doctor, collect(range(0, 6))->map(
        fn (int $weekday): array => [
            'weekday' => $weekday,
            'is_open' => in_array($weekday, [6, 0], true),
            'start_time' => in_array($weekday, [6, 0], true) ? '09:00' : null,
            'end_time' => in_array($weekday, [6, 0], true) ? '11:00' : null,
        ],
    )->all());

    $weeks = $schedule->bookingWeeks($doctor);
    $firstDay = $weeks['this_week'][0] ?? null;

    expect($weeks['has_availability'])->toBeTrue()
        ->and(collect($weeks['this_week'])->pluck('date')->all())
        ->toContain('2026-07-25', '2026-07-26')
        ->and($firstDay['times'][0]['label'] ?? null)->toBe('09:00 صباحاً');
});
