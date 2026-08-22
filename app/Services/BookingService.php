<?php

namespace App\Services;

use App\Enums\AppointmentSource;
use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use App\Support\BookingSlotKey;
use App\Support\TimeFormat;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        private PatientService $patients,
        private ScheduleService $schedule,
    ) {}

    /**
     * @param  array{
     *     name: string,
     *     phone: string,
     *     date: string,
     *     start_time: string,
     *     appointment_type_id: int,
     *     status?: AppointmentStatus
     * }  $data
     */
    public function createInstant(User $doctor, array $data): Appointment
    {
        return $this->create($doctor, $data, AppointmentSource::Instant, $data['status'] ?? AppointmentStatus::Confirmed);
    }

    /**
     * @param  array{
     *     name: string,
     *     phone: string,
     *     date: string,
     *     start_time: string,
     *     appointment_type_id: int
     * }  $data
     */
    public function createPublic(User $doctor, array $data): Appointment
    {
        return $this->create($doctor, $data, AppointmentSource::Public, AppointmentStatus::Pending);
    }

    /**
     * @param  array{
     *     date?: string,
     *     start_time?: string,
     *     appointment_type_id?: int,
     *     status?: string
     * }  $data
     */
    public function update(Appointment $appointment, array $data): Appointment
    {
        return DB::transaction(function () use ($appointment, $data): Appointment {
            $appointment = $this->lockAppointment($appointment);

            if (! $appointment->status->isEditable()) {
                throw ValidationException::withMessages([
                    'status' => 'لا يمكن تعديل المواعيد المكتملة أو الملغاة.',
                ]);
            }

            $doctor = $appointment->user;
            abort_if($doctor === null, 500);

            if (isset($data['date'], $data['start_time'])) {
                $date = Carbon::parse($data['date']);
                $startTime = TimeFormat::normalize($data['start_time']);

                $this->assertSlotReservable($doctor, $date, $startTime, $appointment->id);

                $settings = $this->schedule->getSettings($doctor);
                $endTime = Carbon::parse($startTime)
                    ->addMinutes((int) $settings->appointment_duration_minutes)
                    ->format('H:i:s');

                $appointment->date = $data['date'];
                $appointment->start_time = $startTime;
                $appointment->end_time = $endTime;
            }

            if (isset($data['appointment_type_id'])) {
                $appointment->appointment_type_id = $data['appointment_type_id'];
            }

            if (isset($data['status'])) {
                $appointment->status = AppointmentStatus::from($data['status']);
            }

            $this->saveAppointment($appointment);

            return $appointment->refresh();
        });
    }

    public function cancel(Appointment $appointment, ?string $reason = null): Appointment
    {
        if (! $appointment->status->canCancel()) {
            throw ValidationException::withMessages([
                'status' => 'لا يمكن إلغاء هذا الموعد.',
            ]);
        }

        $appointment->status = AppointmentStatus::Cancelled;
        $appointment->cancellation_reason = $reason;

        $this->saveAppointment($appointment);

        return $appointment->refresh();
    }

    public function updateStatus(Appointment $appointment, AppointmentStatus $status): Appointment
    {
        $appointment->status = $status;

        $this->saveAppointment($appointment);

        return $appointment->refresh();
    }

    /**
     * @param  array{date?: string, status?: string, search?: string, clinic_id?: int|null, doctor_id?: int|null, per_page?: int}  $filters
     */
    public function listForDoctor(User $doctor, array $filters = []): LengthAwarePaginator
    {
        if ($doctor->isAdmin()) {
            return $this->listForMedicalCenter($filters);
        }

        return $this->appointmentQuery($filters)
            ->where('user_id', $doctor->id)
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * @param  array{date?: string, status?: string, search?: string, clinic_id?: int|null, doctor_id?: int|null, per_page?: int}  $filters
     */
    public function listForMedicalCenter(array $filters = []): LengthAwarePaginator
    {
        return $this->appointmentQuery($filters)->paginate($filters['per_page'] ?? 15);
    }

    /**
     * @param  array{date?: string, status?: string, search?: string, clinic_id?: int|null, doctor_id?: int|null}  $filters
     */
    private function appointmentQuery(array $filters)
    {
        return Appointment::query()
            ->with(['patient', 'appointmentType', 'user', 'clinic'])
            ->when(
                filled($filters['status'] ?? null),
                fn ($q) => $q->where('status', $filters['status']),
            )
            ->when(
                filled($filters['clinic_id'] ?? null),
                fn ($q) => $q->where('clinic_id', $filters['clinic_id']),
            )
            ->when(
                filled($filters['doctor_id'] ?? null),
                fn ($q) => $q->where('user_id', $filters['doctor_id']),
            )
            ->when(
                filled($filters['date'] ?? null),
                fn ($q) => $q->whereDate('date', $filters['date']),
            )
            ->when(
                filled($filters['search'] ?? null),
                function ($q) use ($filters): void {
                    $search = trim((string) $filters['search']);

                    $q->whereHas('patient', function ($patient) use ($search): void {
                        $patient->where(function ($inner) use ($search): void {
                            $inner->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                    });
                },
            )
            ->orderByDesc('date')
            ->orderBy('start_time');
    }

    public function findForDoctor(User $doctor, int $appointmentId): Appointment
    {
        return $doctor->appointments()
            ->with(['patient', 'appointmentType', 'clinic', 'user'])
            ->findOrFail($appointmentId);
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function pendingForDoctor(User $doctor, int $limit = 10)
    {
        return $this->dashboardAppointmentsQuery($doctor)
            ->where('status', AppointmentStatus::Pending)
            ->orderBy('date')
            ->orderBy('start_time')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function todayForDoctor(User $doctor)
    {
        return $this->dashboardAppointmentsQuery($doctor)
            ->whereDate('date', today())
            ->whereNot('status', AppointmentStatus::Cancelled)
            ->orderBy('start_time')
            ->get();
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function confirmedForDoctor(User $doctor, int $limit = 10)
    {
        return $this->dashboardAppointmentsQuery($doctor)
            ->where('status', AppointmentStatus::Confirmed)
            ->whereDate('date', '>=', today())
            ->orderBy('date')
            ->orderBy('start_time')
            ->limit($limit)
            ->get();
    }

    /**
     * Upcoming appointments after today (confirmed / pending).
     *
     * @return Collection<int, Appointment>
     */
    public function upcomingForDoctor(User $doctor, int $limit = 10)
    {
        return $this->dashboardAppointmentsQuery($doctor)
            ->whereDate('date', '>', today())
            ->whereIn('status', [
                AppointmentStatus::Pending->value,
                AppointmentStatus::Confirmed->value,
            ])
            ->orderBy('date')
            ->orderBy('start_time')
            ->limit($limit)
            ->get();
    }

    /**
     * Admin sees center-wide appointments; doctors see their own.
     *
     * @return Builder<Appointment>|HasMany<Appointment, User>
     */
    private function dashboardAppointmentsQuery(User $user)
    {
        if ($user->isAdmin()) {
            return Appointment::query()
                ->with(['patient', 'appointmentType', 'clinic', 'user']);
        }

        return $user->appointments()
            ->with(['patient', 'appointmentType', 'clinic', 'user']);
    }

    /**
     * @return array{
     *     today_count: int,
     *     week_count: int,
     *     available_slots: int,
     *     new_patients: int,
     *     pending_count: int,
     *     confirmed_count: int,
     *     upcoming_count: int,
     *     by_type: Collection<int, array{name: string, color: ?string, count: int}>
     * }
     */
    public function dashboardStats(User $user): array
    {
        if ($user->isAdmin()) {
            return $this->dashboardStatsForCenter();
        }

        $weekStart = now()->startOfWeek(Carbon::SATURDAY)->toDateString();
        $weekEnd = now()->endOfWeek(Carbon::FRIDAY)->toDateString();
        $today = today()->toDateString();
        $cancelled = AppointmentStatus::Cancelled->value;
        $pending = AppointmentStatus::Pending->value;
        $confirmed = AppointmentStatus::Confirmed->value;

        $row = $user->appointments()
            ->selectRaw('
                SUM(CASE WHEN date = ? AND status != ? THEN 1 ELSE 0 END) as today_count,
                SUM(CASE WHEN date BETWEEN ? AND ? AND status != ? THEN 1 ELSE 0 END) as week_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = ? AND date >= ? THEN 1 ELSE 0 END) as confirmed_count,
                SUM(CASE WHEN date > ? AND status IN (?, ?) THEN 1 ELSE 0 END) as upcoming_count
            ', [
                $today, $cancelled,
                $weekStart, $weekEnd, $cancelled,
                $pending,
                $confirmed, $today,
                $today, $pending, $confirmed,
            ])
            ->first();

        return [
            'today_count' => (int) ($row->today_count ?? 0),
            'week_count' => (int) ($row->week_count ?? 0),
            'available_slots' => $this->schedule->availableSlots($user, now())->count(),
            'new_patients' => $user->patients()
                ->whereBetween('created_at', [
                    now()->startOfWeek(Carbon::SATURDAY),
                    now()->endOfWeek(Carbon::FRIDAY),
                ])
                ->count(),
            'pending_count' => (int) ($row->pending_count ?? 0),
            'confirmed_count' => (int) ($row->confirmed_count ?? 0),
            'upcoming_count' => (int) ($row->upcoming_count ?? 0),
            'by_type' => $this->bookingsGroupedByType($user),
            'clinics_count' => null,
            'active_doctors_count' => null,
        ];
    }

    /**
     * @return array{
     *     today_count: int,
     *     week_count: int,
     *     available_slots: int,
     *     new_patients: int,
     *     pending_count: int,
     *     confirmed_count: int,
     *     upcoming_count: int,
     *     by_type: Collection<int, array{name: string, color: ?string, count: int}>,
     *     clinics_count: int,
     *     active_doctors_count: int
     * }
     */
    private function dashboardStatsForCenter(): array
    {
        $weekStart = now()->startOfWeek(Carbon::SATURDAY)->toDateString();
        $weekEnd = now()->endOfWeek(Carbon::FRIDAY)->toDateString();
        $today = today()->toDateString();
        $cancelled = AppointmentStatus::Cancelled->value;
        $pending = AppointmentStatus::Pending->value;
        $confirmed = AppointmentStatus::Confirmed->value;

        $row = Appointment::query()
            ->selectRaw('
                SUM(CASE WHEN date = ? AND status != ? THEN 1 ELSE 0 END) as today_count,
                SUM(CASE WHEN date BETWEEN ? AND ? AND status != ? THEN 1 ELSE 0 END) as week_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = ? AND date >= ? THEN 1 ELSE 0 END) as confirmed_count,
                SUM(CASE WHEN date > ? AND status IN (?, ?) THEN 1 ELSE 0 END) as upcoming_count
            ', [
                $today, $cancelled,
                $weekStart, $weekEnd, $cancelled,
                $pending,
                $confirmed, $today,
                $today, $pending, $confirmed,
            ])
            ->first();

        return [
            'today_count' => (int) ($row->today_count ?? 0),
            'week_count' => (int) ($row->week_count ?? 0),
            'available_slots' => 0,
            'new_patients' => Patient::query()
                ->whereBetween('created_at', [
                    now()->startOfWeek(Carbon::SATURDAY),
                    now()->endOfWeek(Carbon::FRIDAY),
                ])
                ->count(),
            'pending_count' => (int) ($row->pending_count ?? 0),
            'confirmed_count' => (int) ($row->confirmed_count ?? 0),
            'upcoming_count' => (int) ($row->upcoming_count ?? 0),
            'by_type' => collect(),
            'clinics_count' => Clinic::query()->count(),
            'active_doctors_count' => User::query()
                ->where('role', UserRole::Doctor)
                ->where('is_active', true)
                ->count(),
        ];
    }

    /**
     * @return Collection<int, array{name: string, color: ?string, count: int}>
     */
    public function bookingsGroupedByType(User $doctor): Collection
    {
        return $doctor->appointmentTypes()
            ->withCount(['appointments' => fn ($query) => $query->whereNot('status', AppointmentStatus::Cancelled)])
            ->ordered()
            ->get()
            ->map(fn ($type): array => [
                'name' => $type->name,
                'color' => $type->color,
                'count' => (int) $type->appointments_count,
            ]);
    }

    /**
     * @param  array{
     *     name: string,
     *     phone: string,
     *     date: string,
     *     start_time: string,
     *     appointment_type_id: int
     * }  $data
     */
    private function create(
        User $doctor,
        array $data,
        AppointmentSource $source,
        AppointmentStatus $status,
    ): Appointment {
        return DB::transaction(function () use ($doctor, $data, $source, $status): Appointment {
            $date = Carbon::parse($data['date']);
            $startTime = TimeFormat::normalize($data['start_time']);

            $this->assertSlotReservable($doctor, $date, $startTime);

            $patient = $this->patients->findOrCreate($doctor, [
                'name' => $data['name'],
                'phone' => $data['phone'],
            ]);

            $settings = $this->schedule->getSettings($doctor);
            $endTime = Carbon::parse($startTime)
                ->addMinutes((int) $settings->appointment_duration_minutes)
                ->format('H:i:s');

            $appointment = $doctor->appointments()->make([
                'patient_id' => $patient->id,
                'date' => $data['date'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'appointment_type_id' => $data['appointment_type_id'],
            ]);
            $appointment->clinic_id = $doctor->clinic_id;
            $appointment->status = $status;
            $appointment->source = $source;

            $this->saveAppointment($appointment);

            return $appointment->refresh();
        });
    }

    private function lockAppointment(Appointment $appointment): Appointment
    {
        return Appointment::query()
            ->whereKey($appointment->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function assertSlotReservable(
        User $doctor,
        CarbonInterface $date,
        string $startTime,
        ?int $exceptAppointmentId = null,
    ): void {
        $slotKey = BookingSlotKey::for($date, $startTime);

        $doctor->appointments()
            ->where('slot_guard_key', $slotKey)
            ->when($exceptAppointmentId, fn ($query, int $id) => $query->where('id', '!=', $id))
            ->lockForUpdate()
            ->get();

        if ($doctor->appointments()
            ->where('slot_guard_key', $slotKey)
            ->when($exceptAppointmentId, fn ($query, int $id) => $query->where('id', '!=', $id))
            ->exists()) {
            throw ValidationException::withMessages([
                'start_time' => 'هذا الوقت محجوز بالفعل.',
            ]);
        }

        if (! $this->schedule->isSlotAvailable($doctor, $date, $startTime, $exceptAppointmentId)) {
            throw ValidationException::withMessages([
                'start_time' => 'هذا الوقت لم يعد متاحاً. يرجى اختيار وقت آخر.',
            ]);
        }
    }

    private function saveAppointment(Appointment $appointment): void
    {
        try {
            $appointment->save();
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'start_time' => 'هذا الوقت محجوز بالفعل.',
            ]);
        }
    }
}
