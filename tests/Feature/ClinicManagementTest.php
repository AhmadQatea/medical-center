<?php

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\User;
use Carbon\Carbon;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

test('admin can create clinic without appointment types route errors', function () {
    $admin = User::factory()->admin()->create();

    actingAs($admin)
        ->post(route('doctor.clinics.store'), [
            'name' => 'عيادة جديدة',
            'specialty' => 'طبيب أسنان',
            'is_active' => '1',
            'display_order' => 0,
        ])
        ->assertRedirect(route('doctor.clinics.index'));

    assertDatabaseHas('clinics', [
        'name' => 'عيادة جديدة',
        'is_active' => true,
    ]);

    actingAs($admin)
        ->get(route('doctor.clinics.index'))
        ->assertOk()
        ->assertSee('عيادة جديدة');
});

test('admin can open clinic create form without appointment types route errors', function () {
    $admin = User::factory()->admin()->create();

    actingAs($admin)
        ->get(route('doctor.clinics.create'))
        ->assertOk()
        ->assertSee('عيادة جديدة');
});

test('admin can delete empty clinic without doctors or appointments', function () {
    $admin = User::factory()->admin()->create();
    $clinic = Clinic::factory()->create(['name' => 'عيادة للحذف']);

    actingAs($admin)
        ->delete(route('doctor.clinics.destroy', $clinic))
        ->assertRedirect(route('doctor.clinics.index'));

    assertDatabaseMissing('clinics', ['id' => $clinic->id]);
});

test('admin can delete clinic even when admin account is linked to it', function () {
    $clinic = Clinic::factory()->create(['name' => 'عيادة المدير']);
    $admin = User::factory()->admin()->create(['clinic_id' => $clinic->id]);

    actingAs($admin)
        ->delete(route('doctor.clinics.destroy', $clinic))
        ->assertRedirect(route('doctor.clinics.index'));

    assertDatabaseMissing('clinics', ['id' => $clinic->id]);
});

test('clinics index always shows delete action', function () {
    $admin = User::factory()->admin()->create();
    Clinic::factory()->create(['name' => 'عيادة الاختبار']);

    actingAs($admin)
        ->get(route('doctor.clinics.index'))
        ->assertOk()
        ->assertSee('حذف');
});

test('admin cannot delete clinic with doctors', function () {
    $admin = User::factory()->admin()->create();
    $clinic = Clinic::factory()->create();
    User::factory()->create(['clinic_id' => $clinic->id, 'role' => UserRole::Doctor]);

    actingAs($admin)
        ->from(route('doctor.clinics.edit', $clinic))
        ->delete(route('doctor.clinics.destroy', $clinic))
        ->assertRedirect(route('doctor.clinics.edit', $clinic))
        ->assertSessionHasErrors('clinic');

    assertDatabaseHas('clinics', ['id' => $clinic->id]);
});

test('admin can delete clinic with past appointments and preserve them', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-22 12:00:00', 'Asia/Damascus'));

    $admin = User::factory()->admin()->create();
    $clinic = Clinic::factory()->create();
    $appointment = Appointment::factory()->create([
        'clinic_id' => $clinic->id,
        'date' => '2026-08-21',
        'start_time' => '10:00:00',
        'status' => AppointmentStatus::Completed,
    ]);

    actingAs($admin)
        ->delete(route('doctor.clinics.destroy', $clinic))
        ->assertRedirect(route('doctor.clinics.index'));

    assertDatabaseMissing('clinics', ['id' => $clinic->id]);
    assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'clinic_id' => null,
    ]);
});

test('admin cannot delete clinic with future appointments', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-22 12:00:00', 'Asia/Damascus'));

    $admin = User::factory()->admin()->create();
    $clinic = Clinic::factory()->create();
    Appointment::factory()->create([
        'clinic_id' => $clinic->id,
        'date' => '2026-08-23',
        'start_time' => '10:00:00',
        'status' => AppointmentStatus::Confirmed,
    ]);

    actingAs($admin)
        ->from(route('doctor.clinics.edit', $clinic))
        ->delete(route('doctor.clinics.destroy', $clinic))
        ->assertRedirect(route('doctor.clinics.edit', $clinic))
        ->assertSessionHasErrors('clinic');

    assertDatabaseHas('clinics', ['id' => $clinic->id]);
});

test('admin cannot delete clinic with later appointment today', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-22 10:00:00', 'Asia/Damascus'));

    $admin = User::factory()->admin()->create();
    $clinic = Clinic::factory()->create();
    Appointment::factory()->create([
        'clinic_id' => $clinic->id,
        'date' => '2026-08-22',
        'start_time' => '15:00:00',
        'status' => AppointmentStatus::Pending,
    ]);

    actingAs($admin)
        ->from(route('doctor.clinics.edit', $clinic))
        ->delete(route('doctor.clinics.destroy', $clinic))
        ->assertRedirect(route('doctor.clinics.edit', $clinic))
        ->assertSessionHasErrors('clinic');

    assertDatabaseHas('clinics', ['id' => $clinic->id]);
});
