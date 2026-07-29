<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkingHour;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkingHour>
 */
class WorkingHourFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'weekday' => fake()->unique()->numberBetween(0, 6),
            'is_open' => true,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ];
    }
}
