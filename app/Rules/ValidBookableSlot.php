<?php

namespace App\Rules;

use App\Models\User;
use App\Services\ScheduleService;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidBookableSlot implements DataAwareRule, ValidationRule
{
    /** @var array<string, mixed> */
    protected array $data = [];

    public function __construct(
        private User $doctor,
        private ?int $exceptAppointmentId = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param  Closure(string, ?string=): void  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $date = (string) ($this->data['date'] ?? '');
        $startTime = (string) ($this->data['start_time'] ?? '');

        if ($date === '' || $startTime === '') {
            return;
        }

        $schedule = app(ScheduleService::class);
        $parsedDate = \Carbon\Carbon::parse(
            $date,
            (string) config('clinic.timezone', config('app.timezone', 'Asia/Damascus')),
        );

        if (! $schedule->isDateBookable($this->doctor, $parsedDate)) {
            $fail('التاريخ المحدد غير متاح للحجز (يوم مغلق أو إجازة).');

            return;
        }

        if (! $schedule->isSlotAvailable($this->doctor, $parsedDate, $startTime, $this->exceptAppointmentId)) {
            $fail('هذا الوقت لم يعد متاحاً. يرجى اختيار وقت آخر.');
        }
    }
}
