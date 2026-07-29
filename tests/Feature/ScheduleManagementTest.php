<?php

use App\Models\Holiday;
use App\Models\User;
use App\Services\ScheduleService;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

test('doctor can view schedule management without fake calendar preview', function () {
    $doctor = User::factory()->create();

    actingAs($doctor)
        ->get(route('doctor.schedule.index'))
        ->assertOk()
        ->assertSee('أيام وساعات العمل')
        ->assertSee('الإجازات')
        ->assertDontSee('معاينة التقويم الشهري')
        ->assertDontSee('اليوم الوطني');
});

test('doctor can save working days and schedule settings', function () {
    $doctor = User::factory()->create();
    app(ScheduleService::class)->ensureWorkingHours($doctor);

    $ordered = [];
    foreach ([6, 0, 1, 2, 3, 4, 5] as $i => $weekday) {
        $ordered[$i] = [
            'weekday' => $weekday,
            'is_open' => $weekday === 6 ? '1' : null,
            'start_time' => $weekday === 6 ? '09:00' : '',
            'end_time' => $weekday === 6 ? '17:00' : '',
        ];
    }

    actingAs($doctor)
        ->put(route('doctor.schedule.update'), [
            'appointment_duration_minutes' => 30,
            'break_duration_minutes' => 10,
            'lunch_enabled' => '1',
            'lunch_start' => '13:00',
            'lunch_end' => '14:00',
            'days' => $ordered,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('doctor.schedule.index'));

    assertDatabaseHas('schedule_settings', [
        'user_id' => $doctor->id,
        'appointment_duration_minutes' => 30,
        'break_duration_minutes' => 10,
        'lunch_enabled' => 1,
    ]);

    assertDatabaseHas('working_hours', [
        'user_id' => $doctor->id,
        'weekday' => 6,
        'is_open' => 1,
    ]);
});

test('doctor can add and remove holidays', function () {
    $doctor = User::factory()->create();

    actingAs($doctor)
        ->post(route('doctor.schedule.holidays.store'), [
            'date' => now()->addDays(3)->toDateString(),
            'title' => 'إجازة شخصية',
        ])
        ->assertRedirect(route('doctor.schedule.index'));

    $holiday = Holiday::query()->where('user_id', $doctor->id)->firstOrFail();

    assertDatabaseHas('holidays', [
        'user_id' => $doctor->id,
        'title' => 'إجازة شخصية',
    ]);

    actingAs($doctor)
        ->delete(route('doctor.schedule.holidays.destroy', $holiday))
        ->assertRedirect(route('doctor.schedule.index'));

    assertDatabaseMissing('holidays', [
        'id' => $holiday->id,
    ]);
});

test('dashboard shows empty states instead of fake appointments', function () {
    $doctor = User::factory()->create(['name' => 'العيادة السنية التخصصية']);

    actingAs($doctor)
        ->get(route('doctor.dashboard'))
        ->assertOk()
        ->assertSee('لا توجد مواعيد اليوم')
        ->assertDontSee('أحمد الغامدي')
        ->assertDontSee('نورة القحطاني');
});
