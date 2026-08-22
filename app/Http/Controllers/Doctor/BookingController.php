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
use App\Services\AdminBookingContextService;
use App\Services\AppointmentTypeService;
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
        private AdminBookingContextService $context,
        private AppointmentTypeService $appointmentTypes,
    ) {}

    public function index(ListBookingsRequest $request): View
    {
        $filters = $request->filters();

        $bookingContext = $this->context->resolveOptionalFilters(
            $request->user(),
            $filters['clinic_id'] ?? null,
            $filters['doctor_id'] ?? null,
        );

        $filters['clinic_id'] = $bookingContext['clinic']?->id;
        $filters['doctor_id'] = $bookingContext['doctor']?->id;

        $appointments = $this->bookings->listForDoctor($request->user(), $filters);
        $whatsappUrls = [];

        foreach ($appointments as $appointment) {
            if ($appointment->status->canSendWhatsApp()) {
                $whatsappUrls[$appointment->id] = $this->whatsapp->patientConfirmationUrl($appointment);
            }
        }

        return view('doctor.bookings.index', [
            'bookingContext' => $bookingContext,
            'appointments' => $appointments,
            'statuses' => AppointmentStatus::options(),
            'whatsappUrls' => $whatsappUrls,
        ]);
    }

    public function instant(Request $request): View|RedirectResponse
    {
        $bookingContext = $this->context->resolve(
            $request->user(),
            $request->integer('clinic_id') ?: null,
            $request->integer('doctor_id') ?: null,
        );

        if ($request->user()->isAdmin()
            && $bookingContext['auto_selected_doctor']
            && $bookingContext['doctor'] !== null
            && ! $request->filled('doctor_id')) {
            return redirect()->route('doctor.bookings.instant', $this->context->queryParams(
                $bookingContext['clinic'],
                $bookingContext['doctor'],
            ));
        }

        $weeks = ['has_availability' => false, 'this_week' => [], 'next_week' => []];
        $appointmentTypes = collect();

        if ($bookingContext['state'] === AdminBookingContextService::STATE_READY) {
            $doctor = $bookingContext['doctor'];
            $weeks = $this->schedule->bookingWeeks($doctor);
            $appointmentTypes = $this->appointmentTypes->activeForDoctor($doctor);
        }

        return view('doctor.bookings.instant', [
            'bookingContext' => $bookingContext,
            'appointmentTypes' => $appointmentTypes,
            'weeks' => $weeks,
        ]);
    }

    public function store(
        StoreInstantBookingRequest $request,
        CreateInstantBookingAction $createInstantBooking,
    ): RedirectResponse {
        $doctor = $request->targetDoctor();
        $appointment = $createInstantBooking->handle($doctor, $request->bookingData());

        return redirect()
            ->route('doctor.bookings.show', $appointment)
            ->with('success', 'تم إنشاء الموعد بنجاح.');
    }

    public function show(Appointment $appointment): View
    {
        $appointment->load(['patient', 'appointmentType', 'clinic', 'user']);

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
            'appointment' => $appointment->load(['patient', 'clinic', 'user']),
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
            ->route('doctor.bookings.index', $this->context->queryParams(
                $appointment->clinic,
                $appointment->user,
            ))
            ->with('success', 'تم إلغاء الموعد.');
    }
}
