<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveAppointmentType implements ValidationRule
{
    public function __construct(private User $doctor) {}

    /**
     * @param  Closure(string, ?string=): void  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_numeric($value)) {
            $fail('نوع الموعد المحدد غير صالح.');

            return;
        }

        $exists = $this->doctor->appointmentTypes()
            ->whereKey((int) $value)
            ->where('is_active', true)
            ->exists();

        if (! $exists) {
            $fail('نوع الموعد المحدد غير متاح.');
        }
    }
}
