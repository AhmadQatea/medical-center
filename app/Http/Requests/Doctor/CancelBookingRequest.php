<?php

namespace App\Http\Requests\Doctor;

use App\Enums\AppointmentStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CancelBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'reason' => 'سبب الإلغاء',
        ];
    }

    public function reason(): ?string
    {
        $reason = $this->validated('reason');

        return filled($reason) ? (string) $reason : null;
    }
}
