<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReorderAppointmentTypesRequest extends FormRequest
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
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['integer', 'distinct'],
        ];
    }

    /**
     * @return list<int>
     */
    public function orderedIds(): array
    {
        /** @var list<int> $order */
        $order = $this->validated('order');

        return array_map(intval(...), $order);
    }
}
