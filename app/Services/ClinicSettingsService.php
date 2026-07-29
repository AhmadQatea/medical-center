<?php

namespace App\Services;

use App\Models\ClinicSetting;
use App\Models\User;

/**
 * Manages clinic identity used by Clinic Settings and the public booking page.
 */
class ClinicSettingsService
{
    /**
     * Get (or ensure) the clinic settings row for the doctor.
     */
    public function get(User $doctor): ClinicSetting
    {
        return $doctor->clinicSetting()->firstOrCreate(
            [],
            $this->defaultAttributes(),
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
     * Resolve the single-clinic doctor (first authenticated clinic owner).
     */
    public function primaryDoctor(): ?User
    {
        return User::query()->orderBy('id')->first();
    }

    /**
     * Update clinic name, specialty, contact, and media paths.
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
        $settings = $this->get($doctor);

        $settings->fill($data);
        $settings->save();

        return $settings->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultAttributes(): array
    {
        return [
            'clinic_name' => (string) config('clinic.name'),
            'specialty' => (string) config('clinic.doctor.specialty', 'طبيب أسنان'),
            'description' => config('clinic.description'),
            'city' => config('clinic.city'),
            'address' => config('clinic.address'),
            'whatsapp_number' => (string) config('clinic.whatsapp'),
            'logo_path' => null,
            'photo_path' => null,
        ];
    }
}
