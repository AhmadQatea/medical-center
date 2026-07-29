<?php

namespace App\Actions\Schedule;

use App\Models\ScheduleSetting;
use App\Models\User;
use App\Services\ScheduleService;
use Illuminate\Support\Facades\DB;

class UpdateScheduleAction
{
    public function __construct(
        private ScheduleService $schedule,
    ) {}

    /**
     * Persist schedule settings and weekday working hours together.
     *
     * @param  array{
     *     appointment_duration_minutes: int,
     *     break_duration_minutes: int,
     *     lunch_enabled: bool,
     *     lunch_start?: string|null,
     *     lunch_end?: string|null,
     *     days: list<array{
     *         weekday: int,
     *         is_open: bool,
     *         start_time?: string|null,
     *         end_time?: string|null
     *     }>
     * }  $data
     */
    public function handle(User $doctor, array $data): ScheduleSetting
    {
        return DB::transaction(function () use ($doctor, $data): ScheduleSetting {
            $settings = $this->schedule->updateSettings($doctor, [
                'appointment_duration_minutes' => $data['appointment_duration_minutes'],
                'break_duration_minutes' => $data['break_duration_minutes'],
                'lunch_enabled' => $data['lunch_enabled'],
                'lunch_start' => $data['lunch_start'] ?? null,
                'lunch_end' => $data['lunch_end'] ?? null,
            ]);

            $this->schedule->syncWorkingHours($doctor, $data['days']);

            return $settings;
        });
    }
}
