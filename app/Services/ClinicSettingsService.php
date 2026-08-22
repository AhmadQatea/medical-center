<?php

namespace App\Services;

use App\Models\ClinicSetting;
use App\Models\User;
use App\Support\BookingCache;

/**
 * Manages public booking profile settings for a doctor (department display + contact).
 */
class ClinicSettingsService
{
    /**
     * Get (or ensure) the clinic settings row for the doctor.
     */
    public function get(User $doctor): ClinicSetting
    {
        return cache()->remember(
            BookingCache::clinicSettingsKey((int) $doctor->id),
            BookingCache::CLINIC_SETTINGS_TTL,
            fn (): ClinicSetting => $doctor->clinicSetting()->firstOrCreate(
                [],
                $this->defaultAttributesFor($doctor),
            ),
        );
    }

    /**
     * Find clinic settings without creating a row.
     */
    public function find(User $doctor): ?ClinicSetting
    {
        return $doctor->clinicSetting;
    }

    /**
     * Resolve the first admin/doctor account (legacy helper).
     */
    public function primaryDoctor(): ?User
    {
        return User::query()->orderBy('id')->first();
    }

    /**
     * Update public booking display fields and contact info.
     *
     * @param  array{
     *     clinic_name?: string,
     *     specialty?: string,
     *     description?: string|null,
     *     city?: string|null,
     *     address?: string|null,
     *     whatsapp_number?: string,
     *     logo_path?: string|null,
     *     photo_path?: string|null
     * }  $data
     */
    public function update(User $doctor, array $data): ClinicSetting
    {
        $settings = $doctor->clinicSetting()->firstOrCreate(
            [],
            $this->defaultAttributesFor($doctor),
        );

        $settings->fill($data);
        $settings->save();

        BookingCache::forgetClinicSettings((int) $doctor->id);

        return $settings->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultAttributesFor(User $doctor): array
    {
        $department = $doctor->clinic;

        return [
            'clinic_name' => $department?->name ?? (string) config('clinic.default_department.name'),
            'specialty' => $doctor->specialty
                ?? $department?->specialty
                ?? (string) config('clinic.default_department.specialty'),
            'description' => $department?->description ?? config('clinic.medical_center.description'),
            'city' => config('clinic.medical_center.city'),
            'address' => config('clinic.medical_center.address'),
            'whatsapp_number' => (string) config('clinic.medical_center.whatsapp'),
            'logo_path' => null,
            'photo_path' => null,
        ];
    }
}
