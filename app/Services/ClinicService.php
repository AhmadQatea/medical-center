<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\User;
use App\Support\BookingCache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClinicService
{
    /**
     * @return Collection<int, Clinic>
     */
    public function listActiveForBooking(): Collection
    {
        return cache()->remember(
            BookingCache::activeClinicsKey(),
            BookingCache::ACTIVE_CLINICS_TTL,
            function (): Collection {
                return Clinic::query()
                    ->active()
                    ->ordered()
                    ->select(['id', 'name', 'slug', 'specialty', 'description', 'is_active', 'display_order'])
                    ->withCount(['doctors as active_doctors_count' => fn ($query) => $query->bookableStaff()])
                    ->get()
                    ->filter(fn (Clinic $clinic): bool => $clinic->active_doctors_count > 0)
                    ->values();
            },
        );
    }

    public function findActiveBySlug(string $slug): Clinic
    {
        $clinic = Clinic::query()
            ->where('slug', $slug)
            ->active()
            ->first();

        abort_if($clinic === null, 404, 'العيادة غير متاحة للحجز حالياً.');

        return $clinic;
    }

    /**
     * @return array{doctor: User, auto_selected: bool}
     */
    public function resolveBookingDoctor(Clinic $clinic, ?int $doctorId = null): array
    {
        $activeDoctors = $clinic->activeDoctors()->get();

        if ($activeDoctors->isEmpty()) {
            throw ValidationException::withMessages([
                'doctor_id' => 'لا يوجد أطباء متاحون حالياً في هذه العيادة.',
            ]);
        }

        if ($doctorId !== null) {
            $doctor = $activeDoctors->firstWhere('id', $doctorId);

            abort_if($doctor === null, 404, 'الطبيب غير متاح في هذه العيادة.');

            return ['doctor' => $doctor, 'auto_selected' => false];
        }

        if ($activeDoctors->count() === 1) {
            return ['doctor' => $activeDoctors->first(), 'auto_selected' => true];
        }

        throw ValidationException::withMessages([
            'doctor_id' => 'يرجى اختيار الطبيب.',
        ]);
    }

    /**
     * @return array{clinic: Clinic, doctor: User}
     */
    public function resolveDoctorEntry(User $doctor): array
    {
        abort_unless($doctor->isBookableStaff(), 404, 'الطبيب غير متاح للحجز حالياً.');

        $clinic = $doctor->clinic;

        abort_if($clinic === null || ! $clinic->is_active, 404, 'العيادة غير متاحة للحجز حالياً.');

        return ['clinic' => $clinic, 'doctor' => $doctor];
    }

    public function assertDoctorBelongsToClinic(Clinic $clinic, User $doctor): void
    {
        if ((int) $doctor->clinic_id !== (int) $clinic->id || ! $doctor->isBookableStaff()) {
            throw ValidationException::withMessages([
                'doctor_id' => 'الطبيب المحدد لا ينتمي إلى هذه العيادة.',
            ]);
        }
    }

    /**
     * @return Collection<int, Clinic>
     */
    public function listAll(): Collection
    {
        return Clinic::query()
            ->ordered()
            ->withCount(['doctors', 'appointments'])
            ->get();
    }

    public function canDelete(Clinic $clinic): bool
    {
        return ! $this->hasDoctors($clinic) && ! $this->hasFutureAppointments($clinic);
    }

    public function hasDoctors(Clinic $clinic): bool
    {
        if (isset($clinic->doctors_count)) {
            return $clinic->doctors_count > 0;
        }

        return $clinic->doctors()->exists();
    }

    public function hasFutureAppointments(Clinic $clinic): bool
    {
        return $clinic->appointments()->futureActive()->exists();
    }

    /**
     * @param  array{
     *     name: string,
     *     slug?: string|null,
     *     description?: string|null,
     *     specialty?: string|null,
     *     image_path?: string|null,
     *     is_active?: bool,
     *     display_order?: int
     * }  $data
     */
    public function create(array $data): Clinic
    {
        $slug = filled($data['slug'] ?? null)
            ? (string) $data['slug']
            : Str::slug((string) $data['name']);

        $clinic = Clinic::query()->create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($slug),
            'description' => $data['description'] ?? null,
            'specialty' => $data['specialty'] ?? null,
            'image_path' => $data['image_path'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'display_order' => $data['display_order'] ?? 0,
        ]);

        BookingCache::forgetActiveClinics();

        return $clinic;
    }

    /**
     * @param  array{
     *     name?: string,
     *     slug?: string|null,
     *     description?: string|null,
     *     specialty?: string|null,
     *     image_path?: string|null,
     *     is_active?: bool,
     *     display_order?: int
     * }  $data
     */
    public function update(Clinic $clinic, array $data): Clinic
    {
        if (array_key_exists('name', $data)) {
            $clinic->name = $data['name'];
        }

        if (array_key_exists('slug', $data) && filled($data['slug'])) {
            $clinic->slug = $this->uniqueSlug((string) $data['slug'], $clinic->id);
        }

        foreach (['description', 'specialty', 'image_path', 'is_active', 'display_order'] as $field) {
            if (array_key_exists($field, $data)) {
                $clinic->{$field} = $data[$field];
            }
        }

        $clinic->save();

        BookingCache::forgetActiveClinics();

        return $clinic->refresh();
    }

    public function toggle(Clinic $clinic): Clinic
    {
        $clinic->is_active = ! $clinic->is_active;
        $clinic->save();

        BookingCache::forgetActiveClinics();

        return $clinic->refresh();
    }

    public function deleteIfAllowed(Clinic $clinic): void
    {
        if ($this->hasDoctors($clinic)) {
            throw ValidationException::withMessages([
                'clinic' => 'لا يمكن حذف العيادة لوجود أطباء مرتبطين بها.',
            ]);
        }

        if ($this->hasFutureAppointments($clinic)) {
            throw ValidationException::withMessages([
                'clinic' => 'لا يمكن حذف العيادة لوجود مواعيد مستقبلية معلقة أو مؤكدة.',
            ]);
        }

        DB::transaction(function () use ($clinic): void {
            $clinic->appointments()->update(['clinic_id' => null]);
            $clinic->delete();
        });

        BookingCache::forgetActiveClinics();
    }

    private function uniqueSlug(string $slug, ?int $exceptId = null): string
    {
        $base = Str::slug($slug) ?: 'clinic';
        $candidate = $base;
        $suffix = 1;

        while (Clinic::query()
            ->when($exceptId !== null, fn ($query) => $query->whereKeyNot($exceptId))
            ->where('slug', $candidate)
            ->exists()) {
            $candidate = "{$base}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }
}
