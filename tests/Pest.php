<?php

use App\Models\AppointmentType;
use App\Models\User;
use App\Services\AppointmentTypeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function bookingWeekdayPayload(array $openWeekdays = []): array
{
    return collect(range(0, 6))
        ->map(fn (int $weekday): array => [
            'weekday' => $weekday,
            'is_open' => in_array($weekday, $openWeekdays, true),
            'start_time' => in_array($weekday, $openWeekdays, true) ? '09:00' : null,
            'end_time' => in_array($weekday, $openWeekdays, true) ? '17:00' : null,
        ])
        ->all();
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function publicBookingPayload(User $doctor, AppointmentType $type, array $overrides = []): array
{
    return array_merge([
        'clinic_id' => $doctor->clinic_id,
        'doctor_id' => $doctor->id,
        'name' => 'مريض QA',
        'phone' => '+963999123456',
        'date' => '2026-07-29',
        'start_time' => '09:00',
        'appointment_type_id' => $type->id,
    ], $overrides);
}

/**
 * @param  array<string, mixed>  $params
 */
function doctorContextRoute(string $routeName, User $doctor, array $params = []): string
{
    return route($routeName, array_merge([
        'clinic_id' => $doctor->clinic_id,
        'doctor_id' => $doctor->id,
    ], $params));
}

/**
 * @return Collection<int, AppointmentType>
 */
function ensureFixedAppointmentTypes(User $doctor): Collection
{
    return app(AppointmentTypeService::class)->ensureForDoctor($doctor);
}
