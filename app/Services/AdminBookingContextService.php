<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AdminBookingContextService
{
    public const STATE_NEEDS_CLINIC = 'needs_clinic';

    public const STATE_NEEDS_DOCTOR = 'needs_doctor';

    public const STATE_NO_DOCTORS = 'no_doctors';

    public const STATE_READY = 'ready';

    public function __construct(
        private ClinicService $clinics,
    ) {}

    /**
     * @return array{
     *     state: string,
     *     clinics: Collection<int, Clinic>,
     *     doctors: Collection<int, User>,
     *     clinic: ?Clinic,
     *     doctor: ?User,
     *     auto_selected_doctor: bool
     * }
     */
    public function resolve(User $actor, ?int $clinicId = null, ?int $doctorId = null): array
    {
        if (! $actor->isAdmin()) {
            return $this->resolveForStaff($actor);
        }

        $clinics = Clinic::query()->active()->ordered()->get();

        if ($clinicId === null) {
            return [
                'state' => self::STATE_NEEDS_CLINIC,
                'clinics' => $clinics,
                'doctors' => collect(),
                'clinic' => null,
                'doctor' => null,
                'auto_selected_doctor' => false,
            ];
        }

        $clinic = $clinics->firstWhere('id', $clinicId)
            ?? Clinic::query()->active()->find($clinicId);

        if ($clinic === null) {
            return [
                'state' => self::STATE_NEEDS_CLINIC,
                'clinics' => $clinics,
                'doctors' => collect(),
                'clinic' => null,
                'doctor' => null,
                'auto_selected_doctor' => false,
            ];
        }

        $doctors = $clinic->activeDoctors()->get();

        if ($doctors->isEmpty()) {
            return [
                'state' => self::STATE_NO_DOCTORS,
                'clinics' => $clinics,
                'doctors' => $doctors,
                'clinic' => $clinic,
                'doctor' => null,
                'auto_selected_doctor' => false,
            ];
        }

        if ($doctorId === null && $doctors->count() === 1) {
            $doctor = $doctors->first();
            $this->clinics->assertDoctorBelongsToClinic($clinic, $doctor);

            return [
                'state' => self::STATE_READY,
                'clinics' => $clinics,
                'doctors' => $doctors,
                'clinic' => $clinic,
                'doctor' => $doctor,
                'auto_selected_doctor' => true,
            ];
        }

        if ($doctorId === null) {
            return [
                'state' => self::STATE_NEEDS_DOCTOR,
                'clinics' => $clinics,
                'doctors' => $doctors,
                'clinic' => $clinic,
                'doctor' => null,
                'auto_selected_doctor' => false,
            ];
        }

        $doctor = $doctors->firstWhere('id', $doctorId);

        if ($doctor === null) {
            return [
                'state' => self::STATE_NEEDS_DOCTOR,
                'clinics' => $clinics,
                'doctors' => $doctors,
                'clinic' => $clinic,
                'doctor' => null,
                'auto_selected_doctor' => false,
            ];
        }

        $this->clinics->assertDoctorBelongsToClinic($clinic, $doctor);

        return [
            'state' => self::STATE_READY,
            'clinics' => $clinics,
            'doctors' => $doctors,
            'clinic' => $clinic,
            'doctor' => $doctor,
            'auto_selected_doctor' => false,
        ];
    }

    /**
     * Optional clinic → doctor filters for listings (show all by default).
     *
     * @return array{
     *     state: string,
     *     clinics: Collection<int, Clinic>,
     *     doctors: Collection<int, User>,
     *     clinic: ?Clinic,
     *     doctor: ?User,
     *     auto_selected_doctor: bool
     * }
     */
    public function resolveOptionalFilters(User $actor, ?int $clinicId = null, ?int $doctorId = null): array
    {
        if (! $actor->isAdmin()) {
            return $this->resolveForStaff($actor);
        }

        $clinics = Clinic::query()->active()->ordered()->get();

        if ($clinicId === null) {
            return [
                'state' => self::STATE_READY,
                'clinics' => $clinics,
                'doctors' => collect(),
                'clinic' => null,
                'doctor' => null,
                'auto_selected_doctor' => false,
            ];
        }

        $clinic = $clinics->firstWhere('id', $clinicId)
            ?? Clinic::query()->active()->find($clinicId);

        if ($clinic === null) {
            return [
                'state' => self::STATE_READY,
                'clinics' => $clinics,
                'doctors' => collect(),
                'clinic' => null,
                'doctor' => null,
                'auto_selected_doctor' => false,
            ];
        }

        $doctors = $clinic->activeDoctors()->get();

        if ($doctors->isEmpty()) {
            return [
                'state' => self::STATE_NO_DOCTORS,
                'clinics' => $clinics,
                'doctors' => $doctors,
                'clinic' => $clinic,
                'doctor' => null,
                'auto_selected_doctor' => false,
            ];
        }

        $doctor = $doctorId !== null
            ? $doctors->firstWhere('id', $doctorId)
            : null;

        if ($doctor !== null) {
            $this->clinics->assertDoctorBelongsToClinic($clinic, $doctor);
        }

        return [
            'state' => self::STATE_READY,
            'clinics' => $clinics,
            'doctors' => $doctors,
            'clinic' => $clinic,
            'doctor' => $doctor,
            'auto_selected_doctor' => false,
        ];
    }

    public function resolveTargetDoctor(User $actor, ?int $clinicId, ?int $doctorId): User
    {
        $context = $this->resolve($actor, $clinicId, $doctorId);

        if ($context['state'] !== self::STATE_READY || $context['doctor'] === null) {
            abort(422, 'يرجى اختيار العيادة والطبيب قبل المتابعة.');
        }

        return $context['doctor'];
    }

    /**
     * @return array<string, int>
     */
    public function queryParams(?Clinic $clinic, ?User $doctor): array
    {
        $params = [];

        if ($clinic !== null) {
            $params['clinic_id'] = $clinic->id;
        }

        if ($doctor !== null) {
            $params['doctor_id'] = $doctor->id;
        }

        return $params;
    }

    /**
     * @return array{
     *     state: string,
     *     clinics: Collection<int, Clinic>,
     *     doctors: Collection<int, User>,
     *     clinic: ?Clinic,
     *     doctor: ?User,
     *     auto_selected_doctor: bool
     * }
     */
    private function resolveForStaff(User $actor): array
    {
        abort_unless($actor->isBookableStaff(), 403);

        $clinic = $actor->clinic;

        abort_if($clinic === null, 403, 'حسابك غير مرتبط بعيادة.');

        return [
            'state' => self::STATE_READY,
            'clinics' => collect([$clinic]),
            'doctors' => collect([$actor]),
            'clinic' => $clinic,
            'doctor' => $actor,
            'auto_selected_doctor' => true,
        ];
    }
}
