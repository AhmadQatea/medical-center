<?php

namespace App\Actions\AppointmentType;

use App\Models\AppointmentType;

class UpdateAppointmentTypeAction
{
    /**
     * @param  array{
     *     name?: string,
     *     description?: string|null,
     *     color?: string|null,
     *     is_active?: bool,
     *     display_order?: int
     * }  $data
     */
    public function handle(AppointmentType $type, array $data): AppointmentType
    {
        $type->fill([
            'name' => $data['name'] ?? $type->name,
            'description' => array_key_exists('description', $data) ? $data['description'] : $type->description,
            'color' => array_key_exists('color', $data) ? $data['color'] : $type->color,
            'is_active' => $data['is_active'] ?? $type->is_active,
            'display_order' => $data['display_order'] ?? $type->display_order,
        ]);

        $type->save();

        return $type->refresh();
    }
}
