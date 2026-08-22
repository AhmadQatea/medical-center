<?php

use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\User;
use App\Services\ClinicSettingsService;
use App\Services\ScheduleService;

use function Pest\Laravel\actingAs;

test('admin bookings index shows all bookings and filters by clinic then doctor', function () {
    $admin = User::factory()->admin()->create();

    $clinicA = Clinic::factory()->create(['name' => 'عيادة الأسنان', 'is_active' => true]);
    $clinicB = Clinic::factory()->create(['name' => 'عيادة الجلدية', 'is_active' => true]);

    $doctorA = User::factory()->create([
        'clinic_id' => $clinicA->id,
        'role' => UserRole::Doctor,
        'name' => 'د. أحمد الأسنان',
        'is_active' => true,
    ]);
    $doctorB = User::factory()->create([
        'clinic_id' => $clinicB->id,
        'role' => UserRole::Doctor,
        'name' => 'د. محمد الجلدية',
        'is_active' => true,
    ]);

    $appointmentA = Appointment::factory()->for($doctorA)->create([
        'clinic_id' => $clinicA->id,
        'date' => '2026-09-01',
        'start_time' => '10:00:00',
    ]);
    $appointmentB = Appointment::factory()->for($doctorB)->create([
        'clinic_id' => $clinicB->id,
        'date' => '2026-09-02',
        'start_time' => '11:00:00',
    ]);
    $appointmentA->patient->update(['name' => 'مريض الأسنان']);
    $appointmentB->patient->update(['name' => 'مريض الجلدية']);

    actingAs($admin)
        ->get(route('doctor.bookings.index'))
        ->assertOk()
        ->assertSee('كل العيادات')
        ->assertSee('مريض الأسنان')
        ->assertSee('مريض الجلدية')
        ->assertDontSee('يرجى اختيار العيادة أولاً');

    actingAs($admin)
        ->get(route('doctor.bookings.index', ['clinic_id' => $clinicA->id]))
        ->assertOk()
        ->assertSee('مريض الأسنان')
        ->assertDontSee('مريض الجلدية')
        ->assertSee('كل الأطباء')
        ->assertSee('د. أحمد الأسنان');

    actingAs($admin)
        ->get(route('doctor.bookings.index', [
            'clinic_id' => $clinicA->id,
            'doctor_id' => $doctorA->id,
        ]))
        ->assertOk()
        ->assertSee('مريض الأسنان')
        ->assertDontSee('مريض الجلدية');
});

test('admin sees only doctors belonging to selected clinic', function () {
    $admin = User::factory()->admin()->create();

    $clinicA = Clinic::factory()->create(['name' => 'عيادة الأسنان', 'is_active' => true]);
    $clinicB = Clinic::factory()->create(['name' => 'عيادة الجلدية', 'is_active' => true]);

    User::factory()->create([
        'clinic_id' => $clinicA->id,
        'role' => UserRole::Doctor,
        'name' => 'د. أحمد الأسنان',
        'is_active' => true,
    ]);
    User::factory()->create([
        'clinic_id' => $clinicA->id,
        'role' => UserRole::Doctor,
        'name' => 'د. سارة الأسنان',
        'is_active' => true,
    ]);
    $doctorB = User::factory()->create([
        'clinic_id' => $clinicB->id,
        'role' => UserRole::Doctor,
        'name' => 'د. محمد الجلدية',
        'is_active' => true,
    ]);

    actingAs($admin)
        ->get(route('doctor.bookings.index', ['clinic_id' => $clinicA->id]))
        ->assertOk()
        ->assertSee('د. أحمد الأسنان')
        ->assertDontSee('د. محمد الجلدية');
});

test('admin auto selects doctor when clinic has one active doctor on schedule page', function () {
    $admin = User::factory()->admin()->create();

    $clinic = Clinic::factory()->create(['is_active' => true]);
    $doctor = User::factory()->create([
        'clinic_id' => $clinic->id,
        'role' => UserRole::Doctor,
        'is_active' => true,
    ]);

    actingAs($admin)
        ->get(route('doctor.schedule.index', ['clinic_id' => $clinic->id]))
        ->assertRedirect(route('doctor.schedule.index', [
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id,
        ]));
});

test('admin bookings index does not force doctor selection for single-doctor clinic', function () {
    $admin = User::factory()->admin()->create();

    $clinic = Clinic::factory()->create(['is_active' => true]);
    User::factory()->create([
        'clinic_id' => $clinic->id,
        'role' => UserRole::Doctor,
        'is_active' => true,
    ]);

    actingAs($admin)
        ->get(route('doctor.bookings.index', ['clinic_id' => $clinic->id]))
        ->assertOk()
        ->assertSee('كل الأطباء');
});

test('admin schedule page shows empty state when clinic has no doctors', function () {
    $admin = User::factory()->admin()->create();
    $clinic = Clinic::factory()->create(['is_active' => true]);

    actingAs($admin)
        ->get(route('doctor.schedule.index', ['clinic_id' => $clinic->id]))
        ->assertOk()
        ->assertSee('لا يوجد أطباء مرتبطون بهذه العيادة');
});

test('admin schedule update rejects doctor from another clinic', function () {
    $admin = User::factory()->admin()->create();

    $clinicA = Clinic::factory()->create(['is_active' => true]);
    $clinicB = Clinic::factory()->create(['is_active' => true]);

    $doctorB = User::factory()->create([
        'clinic_id' => $clinicB->id,
        'role' => UserRole::Doctor,
        'is_active' => true,
    ]);

    app(ClinicSettingsService::class)->get($doctorB);
    $schedule = app(ScheduleService::class);
    $schedule->getSettings($doctorB);

    actingAs($admin)
        ->put(route('doctor.schedule.update'), [
            'clinic_id' => $clinicA->id,
            'doctor_id' => $doctorB->id,
            'appointment_duration_minutes' => 30,
            'break_duration_minutes' => 0,
            'lunch_enabled' => false,
            'days' => bookingWeekdayPayload(openWeekdays: [6]),
        ])
        ->assertSessionHasErrors('doctor_id');
});

test('staff doctor skips clinic selection and manages own schedule', function () {
    $clinic = Clinic::factory()->create(['is_active' => true]);
    $doctor = User::factory()->create([
        'clinic_id' => $clinic->id,
        'role' => UserRole::Doctor,
        'is_active' => true,
    ]);

    app(ClinicSettingsService::class)->get($doctor);
    $schedule = app(ScheduleService::class);
    $schedule->getSettings($doctor);
    $schedule->syncWorkingHours($doctor, bookingWeekdayPayload(openWeekdays: [6]));

    actingAs($doctor)
        ->get(route('doctor.schedule.index'))
        ->assertOk()
        ->assertSee('أيام وساعات العمل');
});
