<?php

namespace App\Http\Requests\Doctor;

use App\Enums\AppointmentStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListBookingsRequest extends FormRequest
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
            'status' => ['nullable', 'string', Rule::in(array_keys(AppointmentStatus::options()))],
            'search' => ['nullable', 'string', 'max:100'],
            'clinic_id' => ['nullable', 'integer', 'exists:clinics,id'],
            'doctor_id' => ['nullable', 'integer', 'exists:users,id'],
            'date' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array{status: ?string, search: ?string, clinic_id: ?int, doctor_id: ?int, date: ?string}
     */
    public function filters(): array
    {
        $clinicId = $this->validated('clinic_id');
        $doctorId = $this->validated('doctor_id');

        return [
            'status' => $this->validated('status'),
            'search' => $this->validated('search'),
            'clinic_id' => $clinicId !== null && $clinicId !== '' ? (int) $clinicId : null,
            'doctor_id' => $doctorId !== null && $doctorId !== '' ? (int) $doctorId : null,
            'date' => $this->validated('date'),
        ];
    }
}
