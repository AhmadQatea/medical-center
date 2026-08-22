<?php

use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\Clinic;
use App\Models\User;
use App\Services\ClinicSettingsService;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

/**
 * @return array{clinic: Clinic, doctors: Collection<int, User>, type: AppointmentType}
 */
function multiClinicBookableSetup(int $doctorCount = 1, int $openWeekday = 6): array
{
    Carbon::setTestNow(Carbon::parse('2026-07-25 08:00:00'));

    $clinic = Clinic::factory()->create(['is_active' => true]);
    $doctors = collect();

    for ($i = 0; $i < $doctorCount; $i++) {
        $doctor = User::factory()->create([
            'clinic_id' => $clinic->id,
            'role' => UserRole::Doctor,
            'is_active' => true,
            'name' => 'د. طبيب '.($i + 1),
        ]);

        app(ClinicSettingsService::class)->get($doctor);

        $schedule = app(ScheduleService::class);
        $schedule->getSettings($doctor);
        $schedule->updateSettings($doctor, [
            'appointment_duration_minutes' => 30,
            'break_duration_minutes' => 0,
            'lunch_enabled' => false,
        ]);
        $schedule->syncWorkingHours($doctor, bookingWeekdayPayload(openWeekdays: [$openWeekday]));

        $doctors->push($doctor);
    }

    $type = AppointmentType::factory()->create([
        'user_id' => $doctors->first()->id,
        'name' => 'معاينة',
        'is_active' => true,
    ]);

    return [
        'clinic' => $clinic,
        'doctors' => $doctors,
        'type' => $type,
    ];
}

test('clinic with multiple doctors shows doctor selection step', function () {
    ['clinic' => $clinic, 'doctors' => $doctors] = multiClinicBookableSetup(2);

    get(route('booking.clinic', $clinic))
        ->assertOk()
        ->assertSee('اختر الطبيب')
        ->assertSee($doctors[0]->name)
        ->assertSee($doctors[1]->name);
});

test('clinic with one doctor auto selects doctor and skips selection step', function () {
    ['clinic' => $clinic, 'doctors' => $doctors] = multiClinicBookableSetup(1);
    $doctor = $doctors->first();

    get(route('booking.clinic', $clinic))
        ->assertRedirect(route('booking.book', [$clinic, $doctor]));

    get(route('booking.book', [$clinic, $doctor]))
        ->assertOk()
        ->assertSee('تم اختيار الطبيب تلقائياً')
        ->assertSee('تأكيد الحجز');
});

test('clinic with zero active doctors cannot continue booking', function () {
    $clinic = Clinic::factory()->create(['is_active' => true]);

    get(route('booking.clinic', $clinic))
        ->assertOk()
        ->assertSee('لا يوجد أطباء متاحون حالياً في هذه العيادة');
});

test('doctor cannot be booked through a different clinic', function () {
    ['clinic' => $clinic, 'doctors' => $doctors, 'type' => $type] = multiClinicBookableSetup(1);
    $doctor = $doctors->first();
    $otherClinic = Clinic::factory()->create(['is_active' => true]);

    post(route('booking.store'), [
        'clinic_id' => $otherClinic->id,
        'doctor_id' => $doctor->id,
        'name' => 'مريض تجريبي',
        'phone' => '+963999123456',
        'date' => '2026-07-25',
        'start_time' => '09:00',
        'appointment_type_id' => $type->id,
    ])->assertSessionHasErrors('doctor_id');
});

test('inactive clinic cannot receive new bookings', function () {
    $clinic = Clinic::factory()->inactive()->create();

    get(route('booking.clinic', $clinic))->assertNotFound();
});

test('inactive doctor cannot receive new bookings', function () {
    ['clinic' => $clinic, 'doctors' => $doctors] = multiClinicBookableSetup(1);
    $doctor = $doctors->first();
    $doctor->forceFill(['is_active' => false])->save();

    get(route('booking.book', [$clinic, $doctor]))->assertNotFound();
});

test('booked time slot cannot be booked twice for the same doctor', function () {
    ['clinic' => $clinic, 'doctors' => $doctors, 'type' => $type] = multiClinicBookableSetup(1);
    $doctor = $doctors->first();

    $payload = [
        'clinic_id' => $clinic->id,
        'doctor_id' => $doctor->id,
        'name' => 'مريض تجريبي',
        'phone' => '+963999123456',
        'date' => '2026-07-25',
        'start_time' => '09:00',
        'appointment_type_id' => $type->id,
    ];

    post(route('booking.store'), $payload)->assertRedirect(route('booking.success'));

    post(route('booking.store'), [
        ...$payload,
        'phone' => '+963999123457',
        'name' => 'مريض آخر',
    ])->assertSessionHasErrors('start_time');
});

test('available slots are specific to the selected doctor', function () {
    ['clinic' => $clinic, 'doctors' => $doctors] = multiClinicBookableSetup(2);
    $firstDoctor = $doctors[0];
    $secondDoctor = $doctors[1];

    Appointment::factory()->create([
        'user_id' => $firstDoctor->id,
        'clinic_id' => $clinic->id,
        'date' => '2026-07-25',
        'start_time' => '09:00:00',
        'end_time' => '09:30:00',
        'status' => 'confirmed',
    ]);

    $firstSlots = app(ScheduleService::class)->availableSlots($firstDoctor, Carbon::parse('2026-07-25'));
    $secondSlots = app(ScheduleService::class)->availableSlots($secondDoctor, Carbon::parse('2026-07-25'));

    expect($firstSlots)->not->toContain('09:00');
    expect($secondSlots)->toContain('09:00');
});

test('doctor specific booking link auto selects clinic and doctor', function () {
    ['clinic' => $clinic, 'doctors' => $doctors] = multiClinicBookableSetup(1);
    $doctor = $doctors->first();

    get(route('booking.doctor', $doctor))
        ->assertRedirect(route('booking.book', [$clinic, $doctor]));
});

test('public booking stores clinic and doctor on appointment', function () {
    ['clinic' => $clinic, 'doctors' => $doctors, 'type' => $type] = multiClinicBookableSetup(1);
    $doctor = $doctors->first();

    post(route('booking.store'), [
        'clinic_id' => $clinic->id,
        'doctor_id' => $doctor->id,
        'name' => 'مريض تجريبي',
        'phone' => '+963999123456',
        'date' => '2026-07-25',
        'start_time' => '09:00',
        'appointment_type_id' => $type->id,
    ])->assertRedirect(route('booking.success'));

    $appointment = Appointment::query()->first();

    expect($appointment)->not->toBeNull()
        ->and($appointment->clinic_id)->toBe($clinic->id)
        ->and($appointment->user_id)->toBe($doctor->id);
});
