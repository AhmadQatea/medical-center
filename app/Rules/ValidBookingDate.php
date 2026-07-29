<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidBookingDate implements ValidationRule
{
    /**
     * @param  Closure(string, ?string=): void  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $timezone = (string) config('clinic.timezone', config('app.timezone', 'Asia/Damascus'));
        $today = now($timezone)->startOfDay();
        $date = Carbon::parse((string) $value, $timezone)->startOfDay();

        if ($date->lt($today)) {
            $fail('لا يمكن الحجز في تاريخ سابق.');
        }
    }
}
