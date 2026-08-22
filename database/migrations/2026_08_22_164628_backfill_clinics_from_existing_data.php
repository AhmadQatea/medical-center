<?php

use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migrate single-clinic data into the default clinic row.
     */
    public function up(): void
    {
        if (Clinic::query()->exists()) {
            return;
        }

        $defaultDepartment = config('clinic.default_department');

        $defaultClinic = Clinic::query()->create([
            'name' => (string) ($defaultDepartment['name'] ?? config('clinic.name')),
            'slug' => (string) ($defaultDepartment['slug'] ?? 'dental'),
            'description' => $defaultDepartment['description'] ?? config('clinic.description'),
            'specialty' => (string) ($defaultDepartment['specialty'] ?? config('clinic.doctor.specialty', 'طبيب أسنان')),
            'is_active' => true,
            'display_order' => 0,
        ]);

        $users = User::query()->orderBy('id')->get();

        if ($users->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($users, $defaultClinic): void {
            $users->each(function (User $user, int $index) use ($defaultClinic): void {
                $user->forceFill([
                    'clinic_id' => $defaultClinic->id,
                    'role' => $index === 0 ? UserRole::Admin : UserRole::Doctor,
                    'is_active' => true,
                    'specialty' => $user->specialty ?? (string) config('clinic.doctor.specialty', 'طبيب أسنان'),
                    'display_order' => $index,
                ])->save();
            });

            Appointment::query()
                ->whereNull('clinic_id')
                ->each(function (Appointment $appointment) use ($defaultClinic): void {
                    $clinicId = $appointment->user?->clinic_id ?? $defaultClinic->id;

                    $appointment->forceFill(['clinic_id' => $clinicId])->save();
                });
        });
    }

    public function down(): void
    {
        Appointment::query()->update(['clinic_id' => null]);

        User::query()->update([
            'clinic_id' => null,
            'role' => UserRole::Doctor->value,
            'is_active' => true,
            'specialty' => null,
            'photo_path' => null,
            'display_order' => 0,
        ]);

        Clinic::query()->delete();
    }
};
