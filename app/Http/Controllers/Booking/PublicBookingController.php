<?php

namespace App\Http\Controllers\Booking;

use App\Actions\Booking\CreatePublicBookingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StorePublicBookingRequest;
use App\Services\ClinicSettingsService;
use App\Services\ScheduleService;
use App\Support\Name;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicBookingController extends Controller
{
    public function __construct(
        private ScheduleService $schedule,
        private ClinicSettingsService $clinicSettings,
    ) {}

    public function index(): View
    {
        $doctor = $this->clinicSettings->primaryDoctor();
        abort_if($doctor === null, 503, 'Clinic is not configured yet.');

        $settings = $this->clinicSettings->get($doctor);
        $weeks = $this->schedule->bookingWeeks($doctor);
        $appointmentTypes = $doctor->appointmentTypes()->active()->ordered()->get();

        return view('booking.index', [
            'doctor' => $doctor,
            'settings' => $settings,
            'doctorInitials' => Name::initials($doctor->name),
            'weeks' => $weeks,
            'appointmentTypes' => $appointmentTypes,
        ]);
    }

    public function store(
        StorePublicBookingRequest $request,
        CreatePublicBookingAction $createPublicBooking,
    ): RedirectResponse {
        $createPublicBooking->handle($request->bookingData());

        return redirect()
            ->route('booking.success')
            ->with('booking_completed', true);
    }

    public function success(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('booking_completed')) {
            return redirect()->route('booking.index');
        }

        $doctor = $this->clinicSettings->primaryDoctor();
        $settings = $doctor ? $this->clinicSettings->get($doctor) : null;

        return view('booking.success', [
            'doctor' => $doctor,
            'settings' => $settings,
        ]);
    }
}
