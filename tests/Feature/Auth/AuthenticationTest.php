<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\seed;

test('login screen can be rendered', function () {
    $response = get('/login');

    $response->assertOk()
        ->assertSee('كلمة المرور')
        ->assertSee('إظهار كلمة المرور', false);
});

test('seeded clinic account can authenticate', function () {
    seed();

    $response = post('/login', [
        'email' => 'clinic@example.com',
        'password' => 'admin123123',
    ]);

    assertAuthenticated();
    $response->assertRedirect(route('doctor.dashboard', absolute: false));
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    assertAuthenticated();
    $response->assertRedirect(route('doctor.dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->post('/logout');

    assertGuest();
    $response->assertRedirect('/');
});
