<?php

namespace App\Http\Requests\Doctor;

use App\Http\Requests\Doctor\Concerns\ResolvesAdminBookingContext;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHolidayRequest extends FormRequest
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
            'date' => [
                'required',
                'date',
                'after_or_equal:today',
                Rule::unique('holidays', 'date')->where(
                    fn ($query) => $query->where('user_id', $doctor->id),
                ),
            ],
            'title' => ['nullable', 'string', 'max:150'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'date' => 'تاريخ الإجازة',
            'title' => 'عنوان الإجازة',
            'note' => 'ملاحظة',
        ];
    }

    /**
     * @return array{date: string, title: string|null, note: string|null}
     */
    public function holidayData(): array
    {
        /** @var array{date: string, title?: string|null, note?: string|null} $validated */
        $validated = $this->validated();

        return [
            'date' => $validated['date'],
            'title' => $validated['title'] ?? null,
            'note' => $validated['note'] ?? null,
        ];
    }
}
