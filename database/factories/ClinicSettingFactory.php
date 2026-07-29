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
            'clinic_name' => 'عيادة الدكتور مصطفى بكرو',
            'specialty' => 'طبيب أسنان',
            'description' => 'ابتسامة أجمل تبدأ بثقة ورعاية احترافية.',
            'city' => 'الرياض',
            'address' => 'حي الياسمين، الرياض',
            'whatsapp_number' => '963959422413',
            'logo_path' => null,
            'photo_path' => null,
        ];
    }
}
