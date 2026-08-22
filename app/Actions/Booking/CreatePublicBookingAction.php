<?php

namespace App\Actions\Booking;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\User;
use App\Services\BookingService;
use App\Services\ClinicService;

class CreatePublicBookingAction
{
    public function __construct(
        private BookingService $bookings,
        private ClinicService $clinics,
    ) {}

    /**
     * @param  array{clinic: Clinic, doctor: User}  $context
     * @param  array{
     *     name: string,
     *     phone: string,
     *     date: string,
     *     start_time: string,
     *     appointment_type_id: int
     * }  $data
     */
    public function handle(array $context, array $data): Appointment
    {
        $clinic = $context['clinic'];
        $doctor = $context['doctor'];

        $this->clinics->assertDoctorBelongsToClinic($clinic, $doctor);

        return $this->bookings->createPublic($doctor, $data);
    }
}
