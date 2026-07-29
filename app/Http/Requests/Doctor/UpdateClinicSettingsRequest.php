<?php

namespace App\Http\Requests\Doctor;

use App\Services\WhatsAppService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClinicSettingsRequest extends FormRequest
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
            'clinic_name' => ['required', 'string', 'max:255'],
            'doctor_name' => ['required', 'string', 'max:255'],
            'specialty' => ['required', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'address' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['required', 'string', 'max:20', 'regex:/^[0-9+]+$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'clinic_name' => 'اسم العيادة',
            'doctor_name' => 'اسم الطبيب',
            'specialty' => 'التخصص',
            'city' => 'المدينة',
            'description' => 'الوصف',
            'address' => 'العنوان',
            'whatsapp' => 'رقم واتساب',
        ];
    }

    /**
     * @return array{
     *     clinic_name: string,
     *     doctor_name: string,
     *     specialty: string,
     *     city: string|null,
     *     description: string|null,
     *     address: string|null,
     *     whatsapp_number: string
     * }
     */
    public function clinicData(): array
    {
        /** @var array{clinic_name: string, doctor_name: string, specialty: string, city?: string|null, description?: string|null, address?: string|null, whatsapp: string} $validated */
        $validated = $this->validated();

        return [
            'clinic_name' => $validated['clinic_name'],
            'doctor_name' => $validated['doctor_name'],
            'specialty' => $validated['specialty'],
            'city' => $validated['city'] ?? null,
            'description' => $validated['description'] ?? null,
            'address' => $validated['address'] ?? null,
            'whatsapp_number' => app(WhatsAppService::class)
                ->normalizeNumber($validated['whatsapp']),
        ];
    }
}
