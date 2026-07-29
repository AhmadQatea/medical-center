<?php

namespace App\Actions\ClinicSettings;

use App\Models\ClinicSetting;
use App\Models\User;
use App\Services\ClinicSettingsService;
use Illuminate\Support\Facades\DB;

class UpdateClinicSettingsAction
{
    public function __construct(
        private ClinicSettingsService $clinicSettings,
    ) {}

    /**
     * Persist clinic settings and optionally the doctor's display name.
     *
     * @param  array{
     *     clinic_name: string,
     *     doctor_name: string,
     *     specialty: string,
     *     city?: string|null,
     *     description?: string|null,
     *     address?: string|null,
     *     whatsapp_number: string
     * }  $data
     */
    public function handle(User $doctor, array $data): ClinicSetting
    {
        return DB::transaction(function () use ($doctor, $data): ClinicSetting {
            $doctor->update([
                'name' => $data['doctor_name'],
            ]);

            return $this->clinicSettings->update($doctor, [
                'clinic_name' => $data['clinic_name'],
                'specialty' => $data['specialty'],
                'city' => $data['city'] ?? null,
                'description' => $data['description'] ?? null,
                'address' => $data['address'] ?? null,
                'whatsapp_number' => $data['whatsapp_number'],
            ]);
        });
    }
}
