<?php

namespace Database\Factories;

use App\Models\AppointmentType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AppointmentType>
 */
class AppointmentTypeFactory extends Factory
{
    protected $model = AppointmentType::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'color' => fake()->optional()->hexColor(),
            'is_active' => true,
            'display_order' => fake()->numberBetween(1, 20),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    public function configure(): static
    {
        return $this->afterMaking(function (AppointmentType $type): void {
            if ($type->user_id === null) {
                $type->user_id = User::factory();
            }
        });
    }
}
