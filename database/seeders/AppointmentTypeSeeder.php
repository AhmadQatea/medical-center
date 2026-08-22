<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\AppointmentTypeService;
use Illuminate\Database\Seeder;

class AppointmentTypeSeeder extends Seeder
{
    public function run(?User $doctor = null): void
    {
        if ($doctor === null) {
            $doctor = User::query()->first();
        }

        if ($doctor === null) {
            return;
        }

        app(AppointmentTypeService::class)->ensureForDoctor($doctor);
    }
}
