<?php

namespace Database\Factories;

use App\Models\ClinicSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClinicSetting>
 */
class ClinicSettingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'clinic_name' => fake()->randomElement([
                'عيادة الأسنان',
                'عيادة الجلدية',
                'عيادة الأطفال',
            ]),
            'specialty' => fake()->randomElement(['طبيب أسنان', 'طبيب جلدية', 'طبيب أطفال']),
            'description' => 'رعاية طبية متخصصة ضمن المركز الطبي.',
            'city' => null,
            'address' => null,
            'whatsapp_number' => '963999123456',
            'logo_path' => null,
            'photo_path' => null,
        ];
    }
}
