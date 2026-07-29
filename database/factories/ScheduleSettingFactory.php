<?php

namespace Database\Factories;

use App\Models\ScheduleSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScheduleSetting>
 */
class ScheduleSettingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'appointment_duration_minutes' => 30,
            'break_duration_minutes' => 0,
            'lunch_enabled' => true,
            'lunch_start' => '13:00:00',
            'lunch_end' => '16:00:00',
        ];
    }
}
