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
        ];
    }

    /**
     * @return array{status: ?string, search: ?string}
     */
    public function filters(): array
    {
        return [
            'status' => $this->validated('status'),
            'search' => $this->validated('search'),
        ];
    }
}
