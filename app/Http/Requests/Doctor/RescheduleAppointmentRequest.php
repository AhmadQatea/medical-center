<?php

namespace App\Http\Requests\Doctor;

use App\Models\Appointment;
use App\Rules\ValidBookableSlot;
use App\Rules\ValidBookingDate;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RescheduleAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $appointment = $this->route('appointment');

        return $appointment instanceof Appointment
            && (int) $appointment->user_id === (int) $this->user()->id
            && $appointment->status->isEditable();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Appointment $appointment */
        $appointment = $this->route('appointment');

        return [
            'date' => ['required', 'date', new ValidBookingDate()],
            'start_time' => [
                'required',
                'date_format:H:i',
                new ValidBookableSlot($this->user(), $appointment->id),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'date' => 'التاريخ',
            'start_time' => 'الوقت',
        ];
    }

    /**
     * @return array{date: string, start_time: string}
     */
    public function scheduleData(): array
    {
        /** @var array{date: string, start_time: string} $validated */
        $validated = $this->validated();

        return $validated;
    }
}
