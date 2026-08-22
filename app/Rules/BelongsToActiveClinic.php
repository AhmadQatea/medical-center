<?php

namespace App\Rules;

use App\Models\Clinic;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class BelongsToActiveClinic implements ValidationRule
{
    public function __construct(private Clinic $clinic) {}

    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_numeric($value)) {
            $fail('الطبيب المحدد غير صالح.');

            return;
        }

        $doctor = User::query()->find((int) $value);

        if ($doctor === null || ! $doctor->isBookableStaff() || (int) $doctor->clinic_id !== (int) $this->clinic->id) {
            $fail('الطبيب المحدد لا ينتمي إلى هذه العيادة أو غير متاح.');
        }
    }
}
