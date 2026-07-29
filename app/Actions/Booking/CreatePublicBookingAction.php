<?php

namespace App\Actions\Booking;

use App\Models\Appointment;
use App\Models\User;
use App\Services\BookingService;
use App\Services\ClinicSettingsService;

class CreatePublicBookingAction
{
    public function __construct(
        private BookingService $bookings,
        private ClinicSettingsService $clinicSettings,
    ) {}

    /**
     * @param  array{
     *     name: string,
     *     phone: string,
     *     date: string,
     *     start_time: string,
     *     appointment_type_id: int
     * }  $data
     */
    public function handle(array $data): Appointment
    {
        $doctor = $this->clinicSettings->primaryDoctor();
        abort_if($doctor === null, 503, 'Clinic is not configured yet.');

        return $this->bookings->createPublic($doctor, $data);
    }
}
