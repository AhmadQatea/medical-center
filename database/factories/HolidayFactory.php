<?php

namespace Database\Factories;

use App\Models\Holiday;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Holiday>
 */
class HolidayFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date' => fake()->unique()->dateTimeBetween('+1 week', '+6 months')->format('Y-m-d'),
            'title' => fake()->optional()->sentence(2),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
