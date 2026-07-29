<?php

namespace Database\Factories;

use App\Enums\AppointmentSource;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'patient_id' => Patient::factory(),
            'date' => fake()->dateTimeBetween('now', '+2 weeks')->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'status' => AppointmentStatus::Pending,
            'source' => AppointmentSource::Public,
            'appointment_type_id' => null,
            'cancellation_reason' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Appointment $appointment): void {
            if ($appointment->appointment_type_id === null && $appointment->user_id !== null) {
                $appointment->appointment_type_id = AppointmentType::factory()->create([
                    'user_id' => $appointment->user_id,
                ])->id;
            }
        })->afterCreating(function (Appointment $appointment): void {
            $patient = $appointment->patient;

            if ($patient !== null && (int) $patient->user_id !== (int) $appointment->user_id) {
                $patient->forceFill(['user_id' => $appointment->user_id])->save();
            }
        })->afterMaking(function (Appointment $appointment): void {
            $appointment->status ??= AppointmentStatus::Pending;
            $appointment->source ??= AppointmentSource::Public;
        });
    }
}
