<?php

use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

test('admin can create doctor with phone only without email or password fields', function () {
    $admin = User::factory()->admin()->create();
    $clinic = Clinic::factory()->create();

    actingAs($admin)
        ->post(route('doctor.doctors.store'), [
            'clinic_id' => $clinic->id,
            'name' => 'د. أحمد محمود',
            'phone' => '0999123456',
            'specialty' => 'طبيب أسنان',
            'is_active' => '1',
            'display_order' => 0,
        ])
        ->assertRedirect(route('doctor.doctors.index'));

    $doctor = User::query()->where('name', 'د. أحمد محمود')->first();

    expect($doctor)->not->toBeNull()
        ->and($doctor->phone)->toBe('0999123456')
        ->and($doctor->role)->toBe(UserRole::Doctor)
        ->and($doctor->email)->toContain('@internal.local');

    assertDatabaseHas('users', [
        'name' => 'د. أحمد محمود',
        'phone' => '0999123456',
        'clinic_id' => $clinic->id,
    ]);
});

test('admin cannot create doctor without phone', function () {
    $admin = User::factory()->admin()->create();
    $clinic = Clinic::factory()->create();

    actingAs($admin)
        ->post(route('doctor.doctors.store'), [
            'clinic_id' => $clinic->id,
            'name' => 'د. بدون هاتف',
            'specialty' => 'طبيب أسنان',
        ])
        ->assertSessionHasErrors('phone');
});

test('admin can update doctor phone without changing credentials', function () {
    $admin = User::factory()->admin()->create();
    $clinic = Clinic::factory()->create();
    $doctor = User::factory()->create([
        'clinic_id' => $clinic->id,
        'role' => UserRole::Doctor,
        'phone' => '0999111111',
        'email' => 'doctor.test@internal.local',
    ]);

    $originalEmail = $doctor->email;

    actingAs($admin)
        ->put(route('doctor.doctors.update', $doctor), [
            'clinic_id' => $clinic->id,
            'name' => $doctor->name,
            'phone' => '0999222222',
            'specialty' => $doctor->specialty,
            'is_active' => '1',
        ])
        ->assertRedirect(route('doctor.doctors.index'));

    expect($doctor->fresh()->phone)->toBe('0999222222')
        ->and($doctor->fresh()->email)->toBe($originalEmail);
});

test('doctor create form does not show email or password fields', function () {
    $admin = User::factory()->admin()->create();

    actingAs($admin)
        ->get(route('doctor.doctors.create'))
        ->assertOk()
        ->assertSee('رقم الهاتف')
        ->assertDontSee('البريد الإلكتروني')
        ->assertDontSee('كلمة المرور');
});

test('admin can delete doctor without appointments', function () {
    $admin = User::factory()->admin()->create();
    $clinic = Clinic::factory()->create();
    $doctor = User::factory()->create([
        'clinic_id' => $clinic->id,
        'role' => UserRole::Doctor,
    ]);

    actingAs($admin)
        ->delete(route('doctor.doctors.destroy', $doctor))
        ->assertRedirect(route('doctor.doctors.index'));

    assertDatabaseMissing('users', ['id' => $doctor->id]);
});

test('admin cannot delete doctor with appointments', function () {
    $admin = User::factory()->admin()->create();
    $doctor = User::factory()->create(['role' => UserRole::Doctor]);
    Appointment::factory()->for($doctor)->create();

    actingAs($admin)
        ->from(route('doctor.doctors.edit', $doctor))
        ->delete(route('doctor.doctors.destroy', $doctor))
        ->assertRedirect(route('doctor.doctors.edit', $doctor))
        ->assertSessionHasErrors('doctor');

    assertDatabaseHas('users', ['id' => $doctor->id]);
});
