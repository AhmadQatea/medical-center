<?php

namespace App\Actions\AppointmentType;

use App\Models\AppointmentType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReorderAppointmentTypesAction
{
    /**
     * @param  list<int>  $orderedIds
     */
    public function handle(User $doctor, array $orderedIds): void
    {
        $ownedIds = $doctor->appointmentTypes()->pluck('id')->all();

        if (count($orderedIds) !== count($ownedIds) || array_diff($orderedIds, $ownedIds) !== []) {
            throw ValidationException::withMessages([
                'order' => 'ترتيب أنواع المواعيد غير صالح.',
            ]);
        }

        DB::transaction(function () use ($orderedIds): void {
            foreach ($orderedIds as $index => $id) {
                AppointmentType::query()
                    ->whereKey($id)
                    ->update(['display_order' => $index + 1]);
            }
        });
    }
}
