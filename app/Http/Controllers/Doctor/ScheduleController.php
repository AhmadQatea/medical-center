<?php

namespace App\Http\Controllers\Doctor;

use App\Actions\Schedule\UpdateScheduleAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StoreHolidayRequest;
use App\Http\Requests\Doctor\UpdateScheduleRequest;
use App\Models\Holiday;
use App\Services\AdminBookingContextService;
use App\Services\ScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function __construct(
        private ScheduleService $schedule,
        private AdminBookingContextService $context,
    ) {}

    public function index(Request $request): View|RedirectResponse
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
            return redirect()->route('doctor.schedule.index', $this->context->queryParams(
                $bookingContext['clinic'],
                $bookingContext['doctor'],
            ));
        }

        $settings = null;
        $workingHours = collect();
        $holidays = collect();
        $hasOpenDays = false;

        if ($bookingContext['state'] === AdminBookingContextService::STATE_READY) {
            $doctor = $bookingContext['doctor'];
            $settings = $this->schedule->getSettings($doctor);
            $workingHours = $this->schedule->ensureWorkingHours($doctor);
            $holidays = $this->schedule->listHolidays($doctor);
            $hasOpenDays = $workingHours->contains(fn ($day): bool => (bool) $day->is_open);
        }

        return view('doctor.schedule.index', [
            'bookingContext' => $bookingContext,
            'settings' => $settings,
            'workingHours' => $workingHours,
            'holidays' => $holidays,
            'hasOpenDays' => $hasOpenDays,
            'scheduleService' => $this->schedule,
        ]);
    }

    public function update(
        UpdateScheduleRequest $request,
        UpdateScheduleAction $updateSchedule,
    ): RedirectResponse {
        $doctor = $request->targetDoctor();
        $updateSchedule->handle($doctor, $request->scheduleData());

        return redirect()
            ->route('doctor.schedule.index', $this->context->queryParams(
                $doctor->clinic,
                $doctor,
            ))
            ->with('success', 'تم حفظ الجدول بنجاح.');
    }

    public function storeHoliday(StoreHolidayRequest $request): RedirectResponse
    {
        $doctor = $request->targetDoctor();
        $this->schedule->createHoliday($doctor, $request->holidayData());

        return redirect()
            ->route('doctor.schedule.index', $this->context->queryParams(
                $doctor->clinic,
                $doctor,
            ))
            ->with('success', 'تمت إضافة الإجازة.');
    }

    public function destroyHoliday(Holiday $holiday): RedirectResponse
    {
        $doctor = $holiday->user;
        $this->schedule->deleteHoliday($holiday);

        return redirect()
            ->route('doctor.schedule.index', $this->context->queryParams(
                $doctor?->clinic,
                $doctor,
            ))
            ->with('success', 'تم حذف الإجازة.');
    }
}
