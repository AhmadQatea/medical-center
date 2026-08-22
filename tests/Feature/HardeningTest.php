<?php

use App\Models\User;
use App\Services\ClinicSettingsService;

use function Pest\Laravel\get;

test('public booking page loads clinic branding from database', function () {
    $doctor = User::factory()->create(['name' => 'د. أحمد']);
    $clinic = $doctor->clinic;
    app(ClinicSettingsService::class)->get($doctor);

    get(route('booking.book', [$clinic, $doctor]))
        ->assertOk()
        ->assertSee('د. أحمد')
        ->assertSee($clinic->name);
});

test('booking success redirects when session proof is missing', function () {
    User::factory()->create();

    get(route('booking.success'))
        ->assertRedirect(route('booking.index'));
});
