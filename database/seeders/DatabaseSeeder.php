<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Clinic;
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

        /** @var array{name: string, slug: string, specialty: string, description: string|null} $department */
        $department = config('clinic.default_department');

        $clinic = Clinic::query()->firstOrCreate(
            ['slug' => $department['slug']],
            [
                'name' => $department['name'],
                'description' => $department['description'],
                'specialty' => $department['specialty'],
                'is_active' => true,
                'display_order' => 0,
            ],
        );

        $admin = User::query()->updateOrCreate(
            ['email' => (string) config('clinic.seed_doctor.email')],
            [
                'name' => (string) config('clinic.seed_doctor.name'),
                'password' => $password,
                'email_verified_at' => now(),
                'role' => UserRole::Admin,
                'clinic_id' => $clinic->id,
                'is_active' => true,
                'specialty' => $department['specialty'],
                'display_order' => 0,
            ],
        );

        app(ClinicSettingsService::class)->get($admin);

        $this->call(AppointmentTypeSeeder::class, false, ['doctor' => $admin]);
    }
}
