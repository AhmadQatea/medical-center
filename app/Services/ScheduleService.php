<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Holiday;
use App\Models\ScheduleSetting;
use App\Models\User;
use App\Models\WorkingHour;
use App\Support\TimeFormat;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Manages schedule rules, working hours, holidays, and available slot calculation.
 *
 * Slot lists are computed at runtime — they are never persisted.
 */
class ScheduleService
{
    /**
     * Arabic weekday labels keyed by Carbon dayOfWeek (0 = Sunday … 6 = Saturday).
     *
     * @var array<int, string>
     */
    private const WEEKDAY_LABELS = [
        0 => 'الأحد',
        1 => 'الإثنين',
        2 => 'الثلاثاء',
        3 => 'الأربعاء',
        4 => 'الخميس',
        5 => 'الجمعة',
        6 => 'السبت',
    ];

    /**
     * Display order for schedule UI (Saturday → Friday).
     *
     * @var list<int>
     */
    public const DISPLAY_WEEKDAY_ORDER = [6, 0, 1, 2, 3, 4, 5];

    /**
     * Get (or ensure) the doctor's global schedule settings.
     */
    public function getSettings(User $doctor): ScheduleSetting
    {
        return $doctor->scheduleSetting()->firstOrCreate(
            [],
            [
                'appointment_duration_minutes' => 30,
                'break_duration_minutes' => 0,
                'lunch_enabled' => false,
                'lunch_start' => null,
                'lunch_end' => null,
            ],
        );
    }

    /**
     * Ensure seven working-hour rows exist (closed by default until the doctor opens them).
     *
     * @return Collection<int, WorkingHour>
     */
    public function ensureWorkingHours(User $doctor): Collection
    {
        foreach (range(0, 6) as $weekday) {
            $doctor->workingHours()->firstOrCreate(
                ['weekday' => $weekday],
                [
                    'is_open' => false,
                    'start_time' => null,
                    'end_time' => null,
                ],
            );
        }

        return $this->workingHoursForDisplay($doctor);
    }

    /**
     * Working hours ordered Saturday → Friday for the schedule form.
     *
     * @return Collection<int, WorkingHour>
     */
    public function workingHoursForDisplay(User $doctor): Collection
    {
        $hours = $doctor->workingHours()->get()->keyBy('weekday');

        return collect(self::DISPLAY_WEEKDAY_ORDER)
            ->map(fn (int $weekday): ?WorkingHour => $hours->get($weekday))
            ->filter()
            ->values();
    }

    /**
     * Persist appointment duration, break gap, and lunch window.
     *
     * @param  array{
     *     appointment_duration_minutes: int,
     *     break_duration_minutes?: int,
     *     lunch_enabled: bool,
     *     lunch_start?: string|null,
     *     lunch_end?: string|null
     * }  $data
     */
    public function updateSettings(User $doctor, array $data): ScheduleSetting
    {
        $settings = $this->getSettings($doctor);

        $settings->fill([
            'appointment_duration_minutes' => $data['appointment_duration_minutes'],
            'break_duration_minutes' => $data['break_duration_minutes'] ?? 0,
            'lunch_enabled' => $data['lunch_enabled'],
            'lunch_start' => $data['lunch_enabled'] ? ($data['lunch_start'] ?? null) : null,
            'lunch_end' => $data['lunch_enabled'] ? ($data['lunch_end'] ?? null) : null,
        ]);
        $settings->save();

        return $settings->refresh();
    }

    /**
     * Replace the doctor's weekday working hours (expected seven day entries).
     *
     * @param  list<array{
     *     weekday: int,
     *     is_open: bool,
     *     start_time?: string|null,
     *     end_time?: string|null
     * }>  $days
     * @return Collection<int, WorkingHour>
     */
    public function syncWorkingHours(User $doctor, array $days): Collection
    {
        return DB::transaction(function () use ($doctor, $days): Collection {
            foreach ($days as $day) {
                $isOpen = (bool) ($day['is_open'] ?? false);

                $doctor->workingHours()->updateOrCreate(
                    ['weekday' => (int) $day['weekday']],
                    [
                        'is_open' => $isOpen,
                        'start_time' => $isOpen ? ($day['start_time'] ?? null) : null,
                        'end_time' => $isOpen ? ($day['end_time'] ?? null) : null,
                    ],
                );
            }

            return $this->workingHoursForDisplay($doctor->fresh());
        });
    }

    /**
     * List holidays for the doctor, ordered by date.
     *
     * @return Collection<int, Holiday>
     */
    public function listHolidays(User $doctor): Collection
    {
        return $doctor->holidays()->orderBy('date')->get();
    }

    /**
     * Add a full-day clinic closure.
     *
     * @param  array{
     *     date: string,
     *     title?: string|null,
     *     note?: string|null
     * }  $data
     */
    public function createHoliday(User $doctor, array $data): Holiday
    {
        return $doctor->holidays()->create([
            'date' => $data['date'],
            'title' => $data['title'] ?? null,
            'note' => $data['note'] ?? null,
        ]);
    }

    /**
     * Remove a holiday closure.
     */
    public function deleteHoliday(Holiday $holiday): void
    {
        $holiday->delete();
    }

    /**
     * Bookable days in a date range that still have at least one free slot.
     *
     * Prefetches settings, hours, holidays, and bookings once for the range
     * (avoids per-day N+1 queries on the public booking page).
     *
     * @return Collection<int, array{
     *     date: string,
     *     weekday_label: string,
     *     day_label: string,
     *     times: list<array{value: string, label: string}>
     * }>
     */
    public function availableDaysInRange(User $doctor, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $fromDay = $from->copy()->timezone($this->clinicTimezone())->startOfDay();
        $toDay = $to->copy()->timezone($this->clinicTimezone())->startOfDay();

        $context = $this->availabilityContext(
            $doctor,
            $fromDay->toDateString(),
            $toDay->toDateString(),
        );

        $days = collect();
        $cursor = $fromDay->copy();

        while ($cursor->lte($toDay)) {
            $times = $this->computeAvailableSlots($cursor, $context);

            if ($times->isNotEmpty()) {
                $days->push([
                    'date' => $cursor->toDateString(),
                    'weekday_label' => self::WEEKDAY_LABELS[$cursor->dayOfWeek],
                    'day_label' => $cursor->locale('ar')->translatedFormat('j F'),
                    'times' => $times->map(fn (string $time): array => [
                        'value' => $time,
                        'label' => TimeFormat::arabic($time),
                    ])->values()->all(),
                ]);
            }

            $cursor->addDay();
        }

        return $days;
    }

    /**
     * This week (Sat–Fri) and next week availability for the public booking UI.
     *
     * @return array{
     *     this_week: list<array{date: string, weekday_label: string, day_label: string, times: list<array{value: string, label: string}>}>,
     *     next_week: list<array{date: string, weekday_label: string, day_label: string, times: list<array{value: string, label: string}>}>,
     *     has_availability: bool
     * }
     */
    public function bookingWeeks(User $doctor, ?CarbonInterface $today = null): array
    {
        $today = ($today ?? $this->clinicNow())->copy()->startOfDay();

        $thisWeekStart = $today->copy()->startOfWeek(CarbonInterface::SATURDAY);
        $thisWeekEnd = $thisWeekStart->copy()->endOfWeek(CarbonInterface::FRIDAY)->startOfDay();

        $nextWeekStart = $thisWeekStart->copy()->addWeek();
        $nextWeekEnd = $nextWeekStart->copy()->endOfWeek(CarbonInterface::FRIDAY)->startOfDay();

        $rangeStart = $today->greaterThan($thisWeekStart) ? $today : $thisWeekStart;
        $context = $this->availabilityContext(
            $doctor,
            $rangeStart->toDateString(),
            $nextWeekEnd->toDateString(),
        );

        $thisWeek = $this->daysFromContext($rangeStart, $thisWeekEnd, $context);
        $nextWeek = $this->daysFromContext($nextWeekStart, $nextWeekEnd, $context);

        return [
            'this_week' => $thisWeek->values()->all(),
            'next_week' => $nextWeek->values()->all(),
            'has_availability' => $thisWeek->isNotEmpty() || $nextWeek->isNotEmpty(),
        ];
    }

    /**
     * Compute bookable start times for a calendar date.
     *
     * @return Collection<int, string> Slot start times (H:i)
     */
    public function availableSlots(User $doctor, CarbonInterface $date, ?int $exceptAppointmentId = null): Collection
    {
        $day = $date->copy()->timezone($this->clinicTimezone())->startOfDay();
        $context = $this->availabilityContext(
            $doctor,
            $day->toDateString(),
            $day->toDateString(),
            $exceptAppointmentId,
        );

        return $this->computeAvailableSlots($day, $context);
    }

    /**
     * Whether the date is open for booking (working day and not a holiday).
     */
    public function isDateBookable(User $doctor, CarbonInterface $date): bool
    {
        $day = $date->copy()->timezone($this->clinicTimezone())->startOfDay();

        if ($day->lt($this->clinicNow()->startOfDay())) {
            return false;
        }

        $context = $this->availabilityContext(
            $doctor,
            $day->toDateString(),
            $day->toDateString(),
        );

        return $this->isDayBookable($day, $context);
    }

    /**
     * Whether a specific start time is still free on the given date.
     */
    public function isSlotAvailable(User $doctor, CarbonInterface $date, string $startTime, ?int $exceptAppointmentId = null): bool
    {
        $normalized = $this->normalizeTime($startTime);

        return $this->availableSlots($doctor, $date, $exceptAppointmentId)->contains($normalized);
    }

    /**
     * Arabic label for a Carbon dayOfWeek index.
     */
    public function weekdayLabel(int $weekday): string
    {
        return self::WEEKDAY_LABELS[$weekday] ?? (string) $weekday;
    }

    /**
     * @return array{
     *     settings: ScheduleSetting,
     *     workingHours: Collection<int, WorkingHour>,
     *     holidays: array<string, true>,
     *     bookedByDate: array<string, array<string, true>>,
     *     now: CarbonInterface
     * }
     */
    private function availabilityContext(
        User $doctor,
        string $fromDate,
        string $toDate,
        ?int $exceptAppointmentId = null,
    ): array {
        $settings = $this->getSettings($doctor);
        $workingHours = $doctor->workingHours()->get()->keyBy('weekday');

        $holidays = $doctor->holidays()
            ->whereDate('date', '>=', $fromDate)
            ->whereDate('date', '<=', $toDate)
            ->pluck('date')
            ->map(function (mixed $date): string {
                if ($date instanceof CarbonInterface) {
                    return $date->toDateString();
                }

                return Carbon::parse((string) $date)->toDateString();
            })
            ->flip()
            ->all();

        $bookedByDate = [];

        $bookedRows = $doctor->appointments()
            ->whereDate('date', '>=', $fromDate)
            ->whereDate('date', '<=', $toDate)
            ->whereIn('status', [
                AppointmentStatus::Pending->value,
                AppointmentStatus::Confirmed->value,
            ])
            ->when($exceptAppointmentId, fn ($q, int $id) => $q->where('id', '!=', $id))
            ->get(['id', 'date', 'start_time']);

        foreach ($bookedRows as $row) {
            $dateKey = $row->date instanceof CarbonInterface
                ? $row->date->toDateString()
                : Carbon::parse((string) $row->date)->toDateString();
            $timeKey = $this->normalizeTime((string) $row->start_time);
            $bookedByDate[$dateKey][$timeKey] = true;
        }

        return [
            'settings' => $settings,
            'workingHours' => $workingHours,
            'holidays' => $holidays,
            'bookedByDate' => $bookedByDate,
            'now' => $this->clinicNow(),
        ];
    }

    /**
     * @param  array{
     *     settings: ScheduleSetting,
     *     workingHours: Collection<int, WorkingHour>,
     *     holidays: array<string, true>,
     *     bookedByDate: array<string, array<string, true>>,
     *     now: CarbonInterface
     * }  $context
     * @return Collection<int, array{date: string, weekday_label: string, day_label: string, times: list<array{value: string, label: string}>}>
     */
    private function daysFromContext(CarbonInterface $from, CarbonInterface $to, array $context): Collection
    {
        $days = collect();
        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $times = $this->computeAvailableSlots($cursor, $context);

            if ($times->isNotEmpty()) {
                $days->push([
                    'date' => $cursor->toDateString(),
                    'weekday_label' => self::WEEKDAY_LABELS[$cursor->dayOfWeek],
                    'day_label' => $cursor->locale('ar')->translatedFormat('j F'),
                    'times' => $times->map(fn (string $time): array => [
                        'value' => $time,
                        'label' => TimeFormat::arabic($time),
                    ])->values()->all(),
                ]);
            }

            $cursor->addDay();
        }

        return $days;
    }

    /**
     * @param  array{
     *     settings: ScheduleSetting,
     *     workingHours: Collection<int, WorkingHour>,
     *     holidays: array<string, true>,
     *     bookedByDate: array<string, array<string, true>>,
     *     now: CarbonInterface
     * }  $context
     */
    private function isDayBookable(CarbonInterface $date, array $context): bool
    {
        $day = $date->copy()->timezone($this->clinicTimezone())->startOfDay();

        if ($day->lt($context['now']->copy()->startOfDay())) {
            return false;
        }

        if (isset($context['holidays'][$day->toDateString()])) {
            return false;
        }

        /** @var WorkingHour|null $workingHour */
        $workingHour = $context['workingHours']->get($day->dayOfWeek);

        return $workingHour !== null
            && $workingHour->is_open
            && $workingHour->start_time !== null
            && $workingHour->end_time !== null;
    }

    /**
     * @param  array{
     *     settings: ScheduleSetting,
     *     workingHours: Collection<int, WorkingHour>,
     *     holidays: array<string, true>,
     *     bookedByDate: array<string, array<string, true>>,
     *     now: CarbonInterface
     * }  $context
     * @return Collection<int, string>
     */
    private function computeAvailableSlots(CarbonInterface $date, array $context): Collection
    {
        $day = $date->copy()->timezone($this->clinicTimezone())->startOfDay();

        if (! $this->isDayBookable($day, $context)) {
            return collect();
        }

        /** @var WorkingHour $workingHour */
        $workingHour = $context['workingHours']->get($day->dayOfWeek);
        $settings = $context['settings'];

        $cursor = $day->copy()->setTimeFromTimeString($this->normalizeTime((string) $workingHour->start_time));
        $windowEnd = $day->copy()->setTimeFromTimeString($this->normalizeTime((string) $workingHour->end_time));
        $duration = max(1, (int) $settings->appointment_duration_minutes);
        $gap = max(0, (int) $settings->break_duration_minutes);
        $step = $duration + $gap;

        $lunchStart = null;
        $lunchEnd = null;

        if ($settings->lunch_enabled && $settings->lunch_start && $settings->lunch_end) {
            $lunchStart = $day->copy()->setTimeFromTimeString($this->normalizeTime((string) $settings->lunch_start));
            $lunchEnd = $day->copy()->setTimeFromTimeString($this->normalizeTime((string) $settings->lunch_end));
        }

        $booked = $context['bookedByDate'][$day->toDateString()] ?? [];
        $now = $context['now'];
        $slots = collect();

        while ($cursor->copy()->addMinutes($duration)->lte($windowEnd)) {
            $slotEnd = $cursor->copy()->addMinutes($duration);
            $time = $cursor->format('H:i');

            $overlapsLunch = $lunchStart !== null
                && $lunchEnd !== null
                && $cursor->lt($lunchEnd)
                && $slotEnd->gt($lunchStart);

            $isPast = $day->isSameDay($now) && $cursor->lte($now);
            $isTaken = isset($booked[$time]);

            if (! $overlapsLunch && ! $isPast && ! $isTaken) {
                $slots->push($time);
            }

            $cursor->addMinutes($step);
        }

        return $slots;
    }

    private function normalizeTime(string $time): string
    {
        return Carbon::parse($time)->format('H:i');
    }

    private function clinicTimezone(): string
    {
        return (string) config('clinic.timezone', config('app.timezone', 'Asia/Damascus'));
    }

    private function clinicNow(): CarbonInterface
    {
        return now($this->clinicTimezone());
    }
}
