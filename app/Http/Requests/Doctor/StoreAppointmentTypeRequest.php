<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentTypeRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('appointment_types', 'name')->where('user_id', $this->user()->id),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم نوع الموعد',
            'description' => 'الوصف',
            'color' => 'اللون',
            'is_active' => 'الحالة',
        ];
    }

    /**
     * @return array{name: string, description?: string|null, color?: string|null, is_active?: bool}
     */
    public function typeData(): array
    {
        /** @var array{name: string, description?: string|null, color?: string|null, is_active?: bool} $validated */
        $validated = $this->validated();

        return $validated;
    }
}
