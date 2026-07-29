<?php

namespace App\Actions\AppointmentType;

use App\Models\AppointmentType;
use Illuminate\Validation\ValidationException;

class DeleteAppointmentTypeAction
{
    public function handle(AppointmentType $type): void
    {
        if ($type->appointments()->exists()) {
            throw ValidationException::withMessages([
                'name' => 'لا يمكن حذف نوع موعد مرتبط بحجوزات موجودة.',
            ]);
        }

        $type->delete();
    }
}
