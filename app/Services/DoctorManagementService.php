<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\BookingCache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DoctorManagementService
{
    public function __construct(private AppointmentTypeService $appointmentTypes) {}

    /**
     * @param  array{
     *     clinic_id: int,
     *     name: string,
     *     phone: string,
     *     specialty?: string|null,
     *     is_active?: bool,
     *     display_order?: int
     * }  $data
     */
    public function create(array $data): User
    {
        $doctor = User::query()->forceCreate([
            'clinic_id' => $data['clinic_id'],
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $this->generateInternalEmail(),
            'password' => Hash::make(Str::password(64)),
            'role' => UserRole::Doctor,
            'is_active' => $data['is_active'] ?? true,
            'specialty' => $data['specialty'] ?? null,
            'display_order' => $data['display_order'] ?? 0,
            'email_verified_at' => null,
        ]);

        $this->appointmentTypes->ensureForDoctor($doctor);

        BookingCache::forgetActiveClinics();

        return $doctor;
    }

    /**
     * @param  array{
     *     clinic_id: int,
     *     name: string,
     *     phone: string,
     *     specialty?: string|null,
     *     is_active?: bool,
     *     display_order?: int
     * }  $data
     */
    public function update(User $doctor, array $data): User
    {
        $doctor->forceFill([
            'clinic_id' => $data['clinic_id'],
            'name' => $data['name'],
            'phone' => $data['phone'],
            'specialty' => $data['specialty'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'display_order' => $data['display_order'] ?? 0,
        ])->save();

        BookingCache::forgetActiveClinics();

        return $doctor;
    }

    public function canDelete(User $doctor): bool
    {
        abort_unless($doctor->isDoctor(), 404);

        if (isset($doctor->appointments_count)) {
            return $doctor->appointments_count === 0;
        }

        return ! $doctor->appointments()->exists();
    }

    public function deleteIfAllowed(User $doctor): void
    {
        abort_unless($doctor->isDoctor(), 404);

        if (! $this->canDelete($doctor)) {
            throw ValidationException::withMessages([
                'doctor' => 'لا يمكن حذف الطبيب لوجود مواعيد مرتبطة به.',
            ]);
        }

        $doctor->delete();

        BookingCache::forgetActiveClinics();
    }

    public function generateInternalEmail(): string
    {
        do {
            $email = 'doctor.'.Str::lower(Str::uuid()->toString()).'@internal.local';
        } while (User::query()->where('email', $email)->exists());

        return $email;
    }
}
