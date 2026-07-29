<?php

namespace App\Http\Controllers\Doctor;

use App\Actions\Booking\ConfirmAppointmentAction;
use App\Actions\Booking\CreateInstantBookingAction;
use App\Actions\Booking\UpdateAppointmentStatusAction;
use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\CancelBookingRequest;
use App\Http\Requests\Doctor\ListBookingsRequest;
use App\Http\Requests\Doctor\RescheduleAppointmentRequest;
use App\Http\Requests\Doctor\StoreInstantBookingRequest;
use App\Http\Requests\Doctor\UpdateAppointmentStatusRequest;
use App\Models\Appointment;
use App\Services\BookingService;
use App\Services\ScheduleService;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookings,
        private ScheduleService $schedule,
        private WhatsAppService $whatsapp,
    ) {}

    public function index(ListBookingsRequest $request): View
    {
        $appointments = $this->bookings->listForDoctor($request->user(), $request->filters());

        $whatsappUrls = [];

        foreach ($appointments as $appointment) {
            if ($appointment->status->canSendWhatsApp()) {
                $whatsappUrls[$appointment->id] = $this->whatsapp->patientConfirmationUrl($appointment);
            }
        }

        return view('doctor.bookings.index', [
            'appointments' => $appointments,
            'statuses' => AppointmentStatus::options(),
            'whatsappUrls' => $whatsappUrls,
        ]);
    }

    public function instant(Request $request): View
    {
        $doctor = $request->user();
        $weeks = $this->schedule->bookingWeeks($doctor);
        $appointmentTypes = $doctor->appointmentTypes()->active()->ordered()->get();

        return view('doctor.bookings.instant', [
            'appointmentTypes' => $appointmentTypes,
            'weeks' => $weeks,
        ]);
    }

    public function store(
        StoreInstantBookingRequest $request,
        CreateInstantBookingAction $createInstantBooking,
    ): RedirectResponse {
        $appointment = $createInstantBooking->handle($request->user(), $request->bookingData());

        return redirect()
            ->route('doctor.bookings.show', $appointment)
            ->with('success', 'تم إنشاء الموعد بنجاح.');
    }

    public function show(Appointment $appointment): View
    {
        $appointment->load(['patient', 'appointmentType']);

        return view('doctor.bookings.show', [
            'appointment' => $appointment,
            'whatsappUrl' => $appointment->status->canSendWhatsApp()
                ? $this->whatsapp->patientConfirmationUrl($appointment)
                : null,
            'confirmationMessage' => $appointment->status->canSendWhatsApp()
                ? $this->whatsapp->confirmationMessage($appointment)
                : null,
        ]);
    }

    public function edit(Appointment $appointment): View
    {
        abort_unless($appointment->status->isEditable(), 403);

        $slots = $this->schedule->availableSlots($appointment->user, $appointment->date, $appointment->id);

        return view('doctor.bookings.edit', [
            'appointment' => $appointment->load('patient'),
            'slots' => $slots,
        ]);
    }

    public function update(
        RescheduleAppointmentRequest $request,
        Appointment $appointment,
    ): RedirectResponse {
        abort_unless($appointment->status->isEditable(), 403);

        $this->bookings->update($appointment, $request->scheduleData());

        return redirect()
            ->route('doctor.bookings.show', $appointment)
            ->with('success', 'تم تحديث الموعد بنجاح.');
    }

    public function confirm(
        Appointment $appointment,
        ConfirmAppointmentAction $confirmAppointment,
    ): RedirectResponse {
        $confirmAppointment->handle($appointment);

        return redirect()
            ->route('doctor.bookings.show', $appointment)
            ->with('success', 'تم تأكيد الموعد. يمكنك الآن إرسال رسالة واتساب للمريض.');
    }

    public function updateStatus(
        UpdateAppointmentStatusRequest $request,
        Appointment $appointment,
        UpdateAppointmentStatusAction $updateStatus,
    ): RedirectResponse {
        $updateStatus->handle($appointment, $request->status(), $request->reason());

        $message = match ($request->status()) {
            AppointmentStatus::Completed => 'تم إكمال الموعد.',
            AppointmentStatus::NoShow => 'تم تسجيل عدم حضور المريض.',
            AppointmentStatus::Cancelled => 'تم إلغاء الموعد.',
            default => 'تم تحديث حالة الموعد.',
        };

        return redirect()
            ->route('doctor.bookings.show', $appointment)
            ->with('success', $message);
    }

    public function destroy(
        CancelBookingRequest $request,
        Appointment $appointment,
        UpdateAppointmentStatusAction $updateStatus,
    ): RedirectResponse {
        $updateStatus->handle(
            $appointment,
            AppointmentStatus::Cancelled,
            $request->reason(),
        );

        return redirect()
            ->route('doctor.bookings.index')
            ->with('success', 'تم إلغاء الموعد.');
    }
}
