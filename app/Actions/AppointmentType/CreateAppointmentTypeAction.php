<?php

namespace App\Actions\AppointmentType;

use App\Models\AppointmentType;
use App\Models\User;

class CreateAppointmentTypeAction
{
    /**
     * @param  array{
     *     name: string,
     *     description?: string|null,
     *     color?: string|null,
     *     is_active?: bool,
     *     display_order?: int
     * }  $data
     */
    public function handle(User $doctor, array $data): AppointmentType
    {
        $nextOrder = (int) $doctor->appointmentTypes()->max('display_order') + 1;

        return $doctor->appointmentTypes()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'display_order' => $data['display_order'] ?? $nextOrder,
        ]);
    }
}
