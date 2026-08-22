<?php

namespace App\Http\Requests\Booking;

use App\Models\Clinic;
use App\Models\User;
use App\Rules\ActiveAppointmentType;
use App\Rules\BelongsToActiveClinic;
use App\Rules\ValidBookableSlot;
use App\Rules\ValidBookingDate;
use App\Services\ClinicService;
use App\Support\SyrianPhone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicBookingRequest extends FormRequest
{
    private ?Clinic $resolvedClinic = null;

    private ?User $resolvedDoctor = null;

    public function authorize(): bool
    {
        return true;
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $clinic = $this->resolveClinic();
        $doctor = $this->resolveDoctor($clinic);

        return [
            'clinic_id' => [
                'required',
                'integer',
                Rule::exists('clinics', 'id')->where('is_active', true),
            ],
            'doctor_id' => [
                'required',
                'integer',
                new BelongsToActiveClinic($clinic),
            ],
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^\+9639\d{8}$/'],
            'date' => ['required', 'date', new ValidBookingDate],
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
            'clinic_id' => 'العيادة',
            'doctor_id' => 'الطبيب',
            'name' => 'الاسم الكامل',
            'phone' => 'رقم واتساب',
            'date' => 'التاريخ',
            'start_time' => 'الوقت',
            'appointment_type_id' => 'نوع الموعد',
        ];
    }

    /**
     * @return array{clinic: Clinic, doctor: User}
     */
    public function bookingContext(): array
    {
        return [
            'clinic' => $this->resolveClinic(),
            'doctor' => $this->resolveDoctor($this->resolveClinic()),
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

        return [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'appointment_type_id' => $validated['appointment_type_id'],
        ];
    }

    private function resolveClinic(): Clinic
    {
        if ($this->resolvedClinic !== null) {
            return $this->resolvedClinic;
        }

        $clinic = Clinic::query()
            ->whereKey((int) $this->input('clinic_id'))
            ->active()
            ->first();

        abort_if($clinic === null, 422, 'العيادة غير متاحة للحجز.');

        return $this->resolvedClinic = $clinic;
    }

    private function resolveDoctor(Clinic $clinic): User
    {
        if ($this->resolvedDoctor !== null) {
            return $this->resolvedDoctor;
        }

        $doctor = User::query()->find((int) $this->input('doctor_id'));

        abort_if($doctor === null, 422, 'الطبيب غير متاح.');

        app(ClinicService::class)->assertDoctorBelongsToClinic($clinic, $doctor);

        return $this->resolvedDoctor = $doctor;
    }
}
