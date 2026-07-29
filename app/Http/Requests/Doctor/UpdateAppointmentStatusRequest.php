<?php

namespace App\Http\Requests\Doctor;

use App\Enums\AppointmentStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentStatusRequest extends FormRequest
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
            'status' => [
                'required',
                'string',
                Rule::in([
                    AppointmentStatus::Completed->value,
                    AppointmentStatus::Cancelled->value,
                    AppointmentStatus::NoShow->value,
                ]),
            ],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'status' => 'الحالة',
            'reason' => 'سبب الإلغاء',
        ];
    }

    public function status(): AppointmentStatus
    {
        return AppointmentStatus::from($this->validated('status'));
    }

    public function reason(): ?string
    {
        return $this->validated('reason');
    }
}
