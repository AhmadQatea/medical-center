<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Manages the doctor's patient directory (no patient authentication).
 */
class PatientService
{
    public function __construct(
        private WhatsAppService $whatsapp,
    ) {}

    public function listForDoctor(User $doctor, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = $doctor->isAdmin()
            ? Patient::query()->with(['user:id,name,clinic_id', 'user.clinic:id,name'])->withCount('appointments')
            : $doctor->patients()->withCount('appointments');

        return $query
            ->when($search, function ($query, string $search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function findForDoctor(User $doctor, int $patientId): Patient
    {
        return $doctor->patients()->findOrFail($patientId);
    }

    /**
     * @param  array{name: string, phone: string, notes?: string|null}  $data
     */
    public function create(User $doctor, array $data): Patient
    {
        return $doctor->patients()->create([
            'name' => $data['name'],
            'phone' => $this->whatsapp->normalizeNumber($data['phone']),
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * @param  array{name?: string, phone?: string|null, notes?: string|null}  $data
     */
    public function update(Patient $patient, array $data): Patient
    {
        if (isset($data['name'])) {
            $patient->name = $data['name'];
        }

        if (array_key_exists('phone', $data) && $data['phone'] !== null) {
            $patient->phone = $this->whatsapp->normalizeNumber($data['phone']);
        }

        if (array_key_exists('notes', $data)) {
            $patient->notes = $data['notes'];
        }

        $patient->save();

        return $patient->refresh();
    }

    public function delete(Patient $patient): void
    {
        $patient->delete();
    }

    /**
     * @param  array{name: string, phone: string, notes?: string|null}  $data
     */
    public function findOrCreate(User $doctor, array $data): Patient
    {
        $phone = $this->whatsapp->normalizeNumber($data['phone']);

        $patient = $doctor->patients()
            ->where('phone', $phone)
            ->first();

        if ($patient !== null) {
            $patient->update(['name' => $data['name']]);

            return $patient->refresh();
        }

        return $this->create($doctor, [
            'name' => $data['name'],
            'phone' => $phone,
            'notes' => $data['notes'] ?? null,
        ]);
    }
}
