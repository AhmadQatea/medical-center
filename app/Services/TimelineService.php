<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use App\Support\TimeFormat;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TimelineService
{
    public function __construct(
        private ScheduleService $schedule,
    ) {}

    /**
     * @return Collection<int, array{
     *     type: string,
     *     time?: string,
     *     time_label?: string,
     *     patient?: string,
     *     phone?: string|null,
     *     note?: string|null,
     *     status?: string,
     *     appointment?: Appointment|null,
     *     current?: bool
     * }>
     */
    public function forToday(User $doctor): Collection
    {
        return $this->forDate($doctor, now());
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function forDate(User $doctor, Carbon $date): Collection
    {
        $appointments = $doctor->appointments()
            ->with('patient')
            ->whereDate('date', $date->toDateString())
            ->whereNot('status', 'cancelled')
            ->orderBy('start_time')
            ->get()
            ->keyBy(fn (Appointment $a) => TimeFormat::normalize((string) $a->start_time));

        $slots = $this->schedule->availableSlots($doctor, $date);
        $allTimes = $appointments->keys()
            ->merge($slots)
            ->unique()
            ->sort()
            ->values();

        $now = now();

        return $allTimes->map(function (string $time) use ($appointments, $date, $now): array {
            $appointment = $appointments->get($time);

            if ($appointment !== null) {
                $slotCarbon = $date->copy()->setTimeFromTimeString($time);
                $isCurrent = $date->isSameDay($now)
                    && $now->between($slotCarbon, $slotCarbon->copy()->addMinutes(30));

                return [
                    'type' => 'appointment',
                    'time' => $time,
                    'time_label' => TimeFormat::arabic($time),
                    'patient' => $appointment->patient?->name ?? '—',
                    'phone' => $appointment->patient?->phone,
                    'note' => $appointment->typeLabel(),
                    'status' => $appointment->status->value,
                    'appointment' => $appointment,
                    'current' => $isCurrent,
                ];
            }

            return [
                'type' => 'available',
                'time' => $time,
                'time_label' => TimeFormat::arabic($time),
                'patient' => '—',
                'note' => 'وقت متاح',
                'status' => 'available',
                'appointment' => null,
                'current' => false,
            ];
        });
    }
}
