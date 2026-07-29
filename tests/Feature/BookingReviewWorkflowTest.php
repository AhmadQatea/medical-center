<?php

use App\Actions\Booking\GenerateBookingConfirmationMessage;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\User;
use App\Services\ClinicSettingsService;
use App\Services\WhatsAppService;
use Carbon\Carbon;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

test('doctor can view booking details page', function () {
    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    $appointment = Appointment::factory()
        ->for($doctor)
        ->create(['status' => AppointmentStatus::Pending]);

    actingAs($doctor)
        ->get(route('doctor.bookings.show', $appointment))
        ->assertOk()
        ->assertSee($appointment->patient->name)
        ->assertSee('تأكيد الحجز')
        ->assertSee('معلومات المريض')
        ->assertSee('معلومات الموعد');
});

test('confirm booking updates status and stays on details page', function () {
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

test('confirmed booking shows whatsapp button with generated message', function () {
    $doctor = User::factory()->create(['name' => 'د. مصطفى بكرو']);
    app(ClinicSettingsService::class)->get($doctor);

    $type = AppointmentType::factory()->create([
        'user_id' => $doctor->id,
        'name' => 'معاينة',
    ]);

    $appointment = Appointment::factory()
        ->for($doctor)
        ->create([
            'status' => AppointmentStatus::Confirmed,
            'appointment_type_id' => $type->id,
        ]);

    $appointment->patient->update([
        'name' => 'أحمد',
        'phone' => '963959422413',
    ]);

    actingAs($doctor)
        ->get(route('doctor.bookings.show', $appointment))
        ->assertOk()
        ->assertSee('إرسال رسالة التأكيد عبر واتساب')
        ->assertSee('السلام عليكم أحمد')
        ->assertSee('معاينة');

    $url = app(WhatsAppService::class)->patientConfirmationUrl($appointment->fresh());

    expect($url)->toContain('https://wa.me/963959422413')
        ->and(urldecode($url))->toContain('تم تأكيد موعدكم');
});

test('doctor can complete and mark no show from confirmed booking', function () {
    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    $appointment = Appointment::factory()
        ->for($doctor)
        ->create(['status' => AppointmentStatus::Confirmed]);

    actingAs($doctor)
        ->patch(route('doctor.bookings.status', $appointment), [
            'status' => AppointmentStatus::Completed->value,
        ])
        ->assertRedirect(route('doctor.bookings.show', $appointment));

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Completed);

    $noShow = Appointment::factory()
        ->for($doctor)
        ->create(['status' => AppointmentStatus::Confirmed]);

    actingAs($doctor)
        ->patch(route('doctor.bookings.status', $noShow), [
            'status' => AppointmentStatus::NoShow->value,
        ])
        ->assertRedirect(route('doctor.bookings.show', $noShow));

    expect($noShow->fresh()->status)->toBe(AppointmentStatus::NoShow);
});

test('generate booking confirmation message action builds arabic message', function () {
    $doctor = User::factory()->create();
    $settings = app(ClinicSettingsService::class)->get($doctor);
    $settings->update(['clinic_name' => 'عيادة الدكتور مصطفى بكرو']);

    $type = AppointmentType::factory()->create([
        'user_id' => $doctor->id,
        'name' => 'تنظيف',
    ]);

    $appointment = Appointment::factory()
        ->for($doctor)
        ->create([
            'appointment_type_id' => $type->id,
            'date' => '2026-08-04',
            'start_time' => '14:30:00',
        ]);

    $appointment->patient->update(['name' => 'سارة']);

    $message = app(GenerateBookingConfirmationMessage::class)->handle($appointment->fresh());

    expect($message)
        ->toContain('السلام عليكم سارة')
        ->toContain('عيادة الدكتور مصطفى بكرو')
        ->toContain('تنظيف')
        ->toContain('يرجى الحضور قبل الموعد بعشر دقائق');
});

test('instant booking redirects to booking details page', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-29 08:00:00', 'Asia/Damascus'));
    $weekday = Carbon::parse('2026-07-29')->dayOfWeek;

    $doctor = User::factory()->create();
    app(ClinicSettingsService::class)->get($doctor);

    $type = AppointmentType::factory()->create([
        'user_id' => $doctor->id,
        'is_active' => true,
    ]);

    $schedule = app(\App\Services\ScheduleService::class);
    $schedule->getSettings($doctor);
    $schedule->updateSettings($doctor, [
        'appointment_duration_minutes' => 30,
        'break_duration_minutes' => 0,
        'lunch_enabled' => false,
    ]);

    $schedule->syncWorkingHours($doctor, bookingWeekdayPayload(openWeekdays: [$weekday]));

    $slots = $schedule->availableSlots($doctor, Carbon::parse('2026-07-29'));
    $slot = $slots->first();

    expect($slot)->not->toBeNull();

    $response = actingAs($doctor)
        ->post(route('doctor.bookings.store'), [
            'name' => 'مريض تجريبي',
            'phone' => '+963959422413',
            'date' => '2026-07-29',
            'start_time' => $slot,
            'appointment_type_id' => $type->id,
            'status' => AppointmentStatus::Confirmed->value,
        ]);

    $appointment = Appointment::query()->latest('id')->first();

    $response->assertRedirect(route('doctor.bookings.show', $appointment));
});
