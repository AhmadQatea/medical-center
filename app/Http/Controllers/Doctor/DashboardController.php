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
        $actor = $request->user();

        return view('doctor.dashboard.index', [
            'todayLabel' => now()->locale('ar')->translatedFormat('l j F Y'),
            'stats' => $this->bookings->dashboardStats($actor),
            'pendingAppointments' => $this->bookings->pendingForDoctor($actor),
            'confirmedAppointments' => $this->bookings->confirmedForDoctor($actor),
            'todayAppointments' => $this->bookings->todayForDoctor($actor),
            'upcomingAppointments' => $this->bookings->upcomingForDoctor($actor),
            'availableSlots' => $actor->isAdmin()
                ? collect()
                : $this->schedule->availableSlots($actor, now()),
        ]);
    }
}
