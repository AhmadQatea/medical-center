<?php

namespace App\Http\Controllers\Booking;

use App\Actions\Booking\CreatePublicBookingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StorePublicBookingRequest;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\User;
use App\Services\AppointmentTypeService;
use App\Services\ClinicService;
use App\Services\ClinicSettingsService;
use App\Services\ScheduleService;
use App\Services\WhatsAppService;
use App\Support\Name;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicBookingController extends Controller
{
    public function __construct(
        private ScheduleService $schedule,
        private ClinicSettingsService $clinicSettings,
        private ClinicService $clinics,
        private AppointmentTypeService $appointmentTypes,
    ) {}

    public function index(): View|RedirectResponse
    {
        $clinicList = $this->clinics->listActiveForBooking();

        if ($clinicList->isEmpty()) {
            return view('booking.no-clinics', [
                'medicalCenterName' => config('clinic.medical_center.name'),
            ]);
        }

        if ($clinicList->count() === 1) {
            return redirect()->route('booking.clinic', $clinicList->first());
        }

        return view('booking.clinics', [
            'clinics' => $clinicList,
            'medicalCenterName' => config('clinic.medical_center.name'),
        ]);
    }

    public function clinic(Clinic $clinic): View|RedirectResponse
    {
        abort_unless($clinic->is_active, 404);

        $doctors = $clinic->activeDoctors()->get();

        if ($doctors->isEmpty()) {
            return view('booking.no-doctors', [
                'clinic' => $clinic,
                'medicalCenterName' => config('clinic.medical_center.name'),
            ]);
        }

        if ($doctors->count() === 1) {
            return redirect()->route('booking.book', [$clinic, $doctors->first()]);
        }

        return view('booking.doctors', [
            'clinic' => $clinic,
            'doctors' => $doctors,
            'medicalCenterName' => config('clinic.medical_center.name'),
        ]);
    }

    public function doctorEntry(User $doctor): RedirectResponse
    {
        ['clinic' => $clinic, 'doctor' => $resolvedDoctor] = $this->clinics->resolveDoctorEntry($doctor);

        return redirect()->route('booking.book', [$clinic, $resolvedDoctor]);
    }

    public function book(Clinic $clinic, User $doctor): View
    {
        abort_unless($clinic->is_active, 404);
        abort_unless($doctor->isBookableStaff(), 404);
        $this->clinics->assertDoctorBelongsToClinic($clinic, $doctor);

        $settings = $this->clinicSettings->get($doctor);
        $weeks = $this->schedule->bookingWeeks($doctor);
        $appointmentTypes = $this->appointmentTypes->activeForDoctor($doctor);

        return view('booking.index', [
            'clinic' => $clinic,
            'doctor' => $doctor,
            'settings' => $settings,
            'doctorInitials' => Name::initials($doctor->name),
            'weeks' => $weeks,
            'appointmentTypes' => $appointmentTypes,
            'autoSelectedDoctor' => $clinic->hasSingleActiveDoctor(),
            'medicalCenterName' => config('clinic.medical_center.name'),
        ]);
    }

    public function store(
        StorePublicBookingRequest $request,
        CreatePublicBookingAction $createPublicBooking,
    ): RedirectResponse {
        $appointment = $createPublicBooking->handle($request->bookingContext(), $request->bookingData());

        return redirect()
            ->route('booking.success')
            ->with([
                'booking_completed' => true,
                'booking_appointment_id' => $appointment->id,
                'booking_clinic_id' => $appointment->clinic_id,
                'booking_doctor_id' => $appointment->user_id,
            ]);
    }

    public function success(Request $request, WhatsAppService $whatsapp): View|RedirectResponse
    {
        if (! $request->session()->has('booking_completed')) {
            return redirect()->route('booking.index');
        }

        $doctor = null;
        $clinic = null;
        $settings = null;
        $appointment = null;
        $whatsappUrl = null;
        $whatsappMessage = null;

        if ($request->session()->has('booking_appointment_id')) {
            $appointment = Appointment::query()
                ->with(['patient', 'appointmentType', 'clinic', 'user'])
                ->find($request->session()->get('booking_appointment_id'));
        }

        if ($appointment !== null) {
            $doctor = $appointment->user;
            $clinic = $appointment->clinic;
            $settings = $this->clinicSettings->get($doctor);
            $whatsappUrl = $whatsapp->centerBookingRequestUrl($appointment);
            $whatsappMessage = $whatsapp->publicBookingRequestMessage($appointment);
        } else {
            if ($request->session()->has('booking_doctor_id')) {
                $doctor = User::query()->find($request->session()->get('booking_doctor_id'));
            }

            if ($request->session()->has('booking_clinic_id')) {
                $clinic = Clinic::query()->find($request->session()->get('booking_clinic_id'));
            }

            if ($doctor !== null) {
                $settings = $this->clinicSettings->get($doctor);
            }
        }

        return view('booking.success', [
            'appointment' => $appointment,
            'clinic' => $clinic,
            'doctor' => $doctor,
            'settings' => $settings,
            'whatsappUrl' => $whatsappUrl,
            'whatsappMessage' => $whatsappMessage,
        ]);
    }
}
