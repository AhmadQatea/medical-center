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
            'clinic_name' => 'العيادة السنية التخصصية',
            'specialty' => 'طبيب أسنان',
            'description' => 'ابتسامة أجمل تبدأ بثقة ورعاية احترافية.',
            'city' => null,
            'address' => null,
            'whatsapp_number' => '963999123456',
            'logo_path' => null,
            'photo_path' => null,
        ];
    }
}
