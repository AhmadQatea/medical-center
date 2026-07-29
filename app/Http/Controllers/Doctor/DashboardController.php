<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Services\BookingService;
use App\Services\ScheduleService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private BookingService $bookings,
        private ScheduleService $schedule,
    ) {}

    public function __invoke(Request $request): View
    {
        $doctor = $request->user();

        return view('doctor.dashboard.index', [
            'todayLabel' => now()->locale('ar')->translatedFormat('l j F Y'),
            'stats' => $this->bookings->dashboardStats($doctor),
            'pendingAppointments' => $this->bookings->pendingForDoctor($doctor),
            'confirmedAppointments' => $this->bookings->confirmedForDoctor($doctor),
            'todayAppointments' => $this->bookings->todayForDoctor($doctor),
            'upcomingAppointments' => $this->bookings->upcomingForDoctor($doctor),
            'availableSlots' => $this->schedule->availableSlots($doctor, now()),
        ]);
    }
}
