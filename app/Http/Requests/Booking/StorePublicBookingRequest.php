<?php

namespace App\Http\Requests\Booking;

use App\Rules\ActiveAppointmentType;
use App\Rules\ValidBookableSlot;
use App\Rules\ValidBookingDate;
use App\Services\ClinicSettingsService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePublicBookingRequest extends FormRequest
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
        $doctor = app(ClinicSettingsService::class)->primaryDoctor();
        abort_if($doctor === null, 503, 'Clinic is not configured yet.');

        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^\+9639\d{8}$/'],
            'date' => ['required', 'date', new ValidBookingDate()],
            'start_time' => ['required', 'date_format:H:i', new ValidBookableSlot($doctor)],
            'appointment_type_id' => ['required', 'integer', new ActiveAppointmentType($doctor)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'الاسم الكامل',
            'phone' => 'رقم واتساب',
            'date' => 'التاريخ',
            'start_time' => 'الوقت',
            'appointment_type_id' => 'نوع الموعد',
        ];
    }

    /**
     * @return array{
     *     name: string,
     *     phone: string,
     *     date: string,
     *     start_time: string,
     *     appointment_type_id: int
     * }
     */
    public function bookingData(): array
    {
        /** @var array{name: string, phone: string, date: string, start_time: string, appointment_type_id: int} $validated */
        $validated = $this->validated();

        return $validated;
    }
}
