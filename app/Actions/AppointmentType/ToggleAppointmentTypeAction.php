<?php

namespace App\Actions\AppointmentType;

use App\Models\AppointmentType;

class ToggleAppointmentTypeAction
{
    public function handle(AppointmentType $type): AppointmentType
    {
        $type->update(['is_active' => ! $type->is_active]);

        return $type->refresh();
    }
}
