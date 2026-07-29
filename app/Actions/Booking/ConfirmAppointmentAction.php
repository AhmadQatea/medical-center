<?php

namespace App\Actions\Booking;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConfirmAppointmentAction
{
    public function __construct(
        private UpdateAppointmentStatusAction $updateStatus,
    ) {}

    public function handle(Appointment $appointment): Appointment
    {
        return DB::transaction(function () use ($appointment): Appointment {
            $locked = Appointment::query()
                ->whereKey($appointment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === AppointmentStatus::Cancelled) {
                throw ValidationException::withMessages([
                    'status' => 'لا يمكن تأكيد موعد ملغي.',
                ]);
            }

            if ($locked->status === AppointmentStatus::Completed) {
                throw ValidationException::withMessages([
                    'status' => 'لا يمكن تأكيد موعد مكتمل.',
                ]);
            }

            if ($locked->status !== AppointmentStatus::Pending) {
                throw ValidationException::withMessages([
                    'status' => 'يمكن تأكيد المواعيد قيد الانتظار فقط.',
                ]);
            }

            return $this->updateStatus->handle($locked, AppointmentStatus::Confirmed);
        });
    }
}
