<?php

namespace Database\Factories;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Clinic>
 */
class ClinicFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'عيادة الأسنان',
            'عيادة الجلدية',
            'عيادة الأطفال',
            'عيادة الباطنية',
            'عيادة العظام',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug(Str::ascii($name)).'-'.fake()->unique()->numerify('##'),
            'description' => fake()->optional()->sentence(),
            'specialty' => fake()->randomElement(['طبيب أسنان', 'طبيب جلدية', 'طبيب أطفال', 'طبيب باطنية', 'طبيب عظام']),
            'image_path' => null,
            'is_active' => true,
            'display_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
