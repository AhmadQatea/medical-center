<?php

namespace App\Services;

use App\Models\AppointmentType;
use App\Models\User;
use Illuminate\Support\Collection;

class AppointmentTypeService
{
    /**
     * @return Collection<int, AppointmentType>
     */
    public function ensureForDoctor(User $doctor): Collection
    {
        /** @var list<array{name: string, color: string, display_order: int}> $fixedTypes */
        $fixedTypes = config('appointment_types.fixed', []);
        $fixedNames = collect($fixedTypes)->pluck('name')->all();

        foreach ($fixedTypes as $definition) {
            $doctor->appointmentTypes()->updateOrCreate(
                ['name' => $definition['name']],
                [
                    'color' => $definition['color'],
                    'display_order' => $definition['display_order'],
                    'is_active' => true,
                    'description' => null,
                ],
            );
        }

        if ($fixedNames !== []) {
            $doctor->appointmentTypes()
                ->whereNotIn('name', $fixedNames)
                ->update(['is_active' => false]);
        }

        return $doctor->appointmentTypes()->active()->ordered()->get();
    }

    /**
     * @return Collection<int, AppointmentType>
     */
    public function activeForDoctor(User $doctor): Collection
    {
        $types = $doctor->appointmentTypes()->active()->ordered()->get();

        if ($types->isNotEmpty()) {
            return $types;
        }

        return $this->ensureForDoctor($doctor);
    }
}
