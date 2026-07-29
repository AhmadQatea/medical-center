<?php

namespace App\Http\Controllers\Doctor;

use App\Actions\Schedule\UpdateScheduleAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StoreHolidayRequest;
use App\Http\Requests\Doctor\UpdateScheduleRequest;
use App\Models\Holiday;
use App\Services\ScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function __construct(
        private ScheduleService $schedule,
    ) {}

    /**
     * Display schedule management (settings, working hours, holidays).
     */
    public function index(Request $request): View
    {
        $doctor = $request->user();
        $settings = $this->schedule->getSettings($doctor);
        $workingHours = $this->schedule->ensureWorkingHours($doctor);
        $holidays = $this->schedule->listHolidays($doctor);

        return view('doctor.schedule.index', [
            'settings' => $settings,
            'workingHours' => $workingHours,
            'holidays' => $holidays,
            'scheduleService' => $this->schedule,
        ]);
    }

    /**
     * Persist schedule settings and working hours.
     */
    public function update(
        UpdateScheduleRequest $request,
        UpdateScheduleAction $updateSchedule,
    ): RedirectResponse {
        $updateSchedule->handle($request->user(), $request->scheduleData());

        return redirect()
            ->route('doctor.schedule.index')
            ->with('success', 'تم حفظ جدول العيادة بنجاح.');
    }

    /**
     * Add a full-day holiday closure.
     */
    public function storeHoliday(StoreHolidayRequest $request): RedirectResponse
    {
        $this->schedule->createHoliday($request->user(), $request->holidayData());

        return redirect()
            ->route('doctor.schedule.index')
            ->with('success', 'تمت إضافة الإجازة.');
    }

    /**
     * Remove a holiday closure owned by the authenticated doctor.
     */
    public function destroyHoliday(Holiday $holiday): RedirectResponse
    {
        $this->schedule->deleteHoliday($holiday);

        return redirect()
            ->route('doctor.schedule.index')
            ->with('success', 'تم حذف الإجازة.');
    }
}
