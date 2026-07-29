<?php

namespace App\Actions\Booking;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Services\BookingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateAppointmentStatusAction
{
    public function __construct(
        private BookingService $bookings,
    ) {}

    public function handle(Appointment $appointment, AppointmentStatus $status, ?string $reason = null): Appointment
    {
        return DB::transaction(function () use ($appointment, $status, $reason): Appointment {
            $locked = Appointment::query()
                ->whereKey($appointment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->status->canTransitionTo($status)) {
                throw ValidationException::withMessages([
                    'status' => 'لا يمكن تغيير حالة الموعد إلى '.$status->label().'.',
                ]);
            }

            if ($status === AppointmentStatus::Cancelled) {
                return $this->bookings->cancel($locked, $reason);
            }

            return $this->bookings->updateStatus($locked, $status);
        });
    }
}
