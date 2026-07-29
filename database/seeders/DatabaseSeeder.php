<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\ClinicSettingsService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = config('clinic.seed_doctor.password');

        if ($password === null || $password === '') {
            if (! app()->environment(['local', 'testing'])) {
                throw new RuntimeException('Set DOCTOR_PASSWORD before seeding outside local/testing.');
            }

            $password = 'admin123123';
        }

        $doctor = User::query()->updateOrCreate(
            ['email' => (string) config('clinic.seed_doctor.email')],
            [
                'name' => (string) config('clinic.seed_doctor.name'),
                'password' => $password,
                'email_verified_at' => now(),
            ],
        );

        app(ClinicSettingsService::class)->get($doctor);

        $this->call(AppointmentTypeSeeder::class, false, ['doctor' => $doctor]);
    }
}
