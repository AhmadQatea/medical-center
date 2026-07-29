<?php

namespace Database\Seeders;

use App\Models\AppointmentType;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppointmentTypeSeeder extends Seeder
{
    /**
     * Default dental appointment types for a new clinic.
     */
    public function run(?User $doctor = null): void
    {
        if ($doctor === null) {
            $doctor = User::query()->first();
        }

        if ($doctor === null) {
            return;
        }

        $defaults = [
            ['name' => 'معاينة', 'color' => '#6B1E2A', 'display_order' => 1],
            ['name' => 'مراجعة', 'color' => '#C9A84C', 'display_order' => 2],
            ['name' => 'تنظيف', 'color' => '#0D9488', 'display_order' => 3],
            ['name' => 'حشو', 'color' => '#2563EB', 'display_order' => 4],
            ['name' => 'خلع', 'color' => '#DC2626', 'display_order' => 5],
            ['name' => 'تقويم', 'color' => '#7C3AED', 'display_order' => 6],
            ['name' => 'زراعة', 'color' => '#059669', 'display_order' => 7],
            ['name' => 'تبييض', 'color' => '#0891B2', 'display_order' => 8],
            ['name' => 'استشارة', 'color' => '#4B5563', 'display_order' => 9],
        ];

        foreach ($defaults as $type) {
            $doctor->appointmentTypes()->firstOrCreate(
                ['name' => $type['name']],
                [
                    'color' => $type['color'],
                    'display_order' => $type['display_order'],
                    'is_active' => true,
                ],
            );
        }
    }
}
