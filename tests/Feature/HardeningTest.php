<?php

use App\Models\User;
use App\Services\ClinicSettingsService;

use function Pest\Laravel\get;

test('public booking page loads clinic branding from database', function () {
    $doctor = User::factory()->create(['name' => 'العيادة السنية التخصصية']);
    app(ClinicSettingsService::class)->get($doctor);

    get(route('booking.index'))
        ->assertOk()
        ->assertSee('العيادة السنية التخصصية')
        ->assertSee(config('clinic.name'));
});

test('booking success redirects when session proof is missing', function () {
    User::factory()->create();

    get(route('booking.success'))
        ->assertRedirect(route('booking.index'));
});
