<?php

namespace App\Actions\Booking;

use App\Models\Appointment;
use App\Models\User;
use App\Services\BookingService;

class CreateInstantBookingAction
{
    public function __construct(
        private BookingService $bookings,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(User $doctor, array $data): Appointment
    {
        return $this->bookings->createInstant($doctor, $data);
    }
}
