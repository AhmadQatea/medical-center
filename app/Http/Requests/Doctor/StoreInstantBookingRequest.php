<?php

namespace App\Http\Requests\Doctor;

use App\Enums\AppointmentStatus;
use App\Http\Requests\Doctor\Concerns\ResolvesAdminBookingContext;
use App\Rules\ActiveAppointmentType;
use App\Rules\ValidBookableSlot;
use App\Rules\ValidBookingDate;
use App\Support\SyrianPhone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInstantBookingRequest extends FormRequest
{
    use ResolvesAdminBookingContext;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $doctor = $this->targetDoctor();

        return [
            'clinic_id' => ['nullable', 'integer', 'exists:clinics,id'],
            'doctor_id' => ['nullable', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^\+9639\d{8}$/'],
            'date' => ['required', 'date', new ValidBookingDate],
            'start_time' => ['required', 'date_format:H:i', new ValidBookableSlot($doctor)],
            'appointment_type_id' => ['required', 'integer', new ActiveAppointmentType($doctor)],
            'status' => ['nullable', 'string', Rule::in([
                AppointmentStatus::Confirmed->value,
                AppointmentStatus::Pending->value,
            ])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم المريض',
            'phone' => 'رقم واتساب',
            'date' => 'التاريخ',
            'start_time' => 'الوقت',
            'appointment_type_id' => 'نوع الموعد',
            'status' => 'الحالة',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('phone')) {
            $this->merge([
                'phone' => SyrianPhone::normalize((string) $this->input('phone')),
            ]);
        }

        if ($this->filled('start_time') && preg_match('/^\d{2}:\d{2}:\d{2}$/', (string) $this->input('start_time'))) {
            $this->merge([
                'start_time' => substr((string) $this->input('start_time'), 0, 5),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function bookingData(): array
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'appointment_type_id' => (int) $validated['appointment_type_id'],
            'status' => isset($validated['status'])
                ? AppointmentStatus::from($validated['status'])
                : AppointmentStatus::Confirmed,
        ];
    }
}
