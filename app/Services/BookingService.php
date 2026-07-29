<?php

namespace App\Services;

use App\Enums\AppointmentSource;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use App\Support\BookingSlotKey;
use App\Support\TimeFormat;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
     * @param  array{date?: string, status?: string, search?: string, per_page?: int}  $filters
     */
    public function listForDoctor(User $doctor, array $filters = []): LengthAwarePaginator
    {
        return $doctor->appointments()
            ->with(['patient', 'appointmentType'])
            ->when(
                filled($filters['status'] ?? null),
                fn ($q) => $q->where('status', $filters['status']),
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
            ->orderBy('start_time')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function findForDoctor(User $doctor, int $appointmentId): Appointment
    {
        return $doctor->appointments()
            ->with(['patient', 'appointmentType'])
            ->findOrFail($appointmentId);
    }

    /**
     * @return \Illuminate\Support\Collection<int, Appointment>
     */
    public function pendingForDoctor(User $doctor, int $limit = 10)
    {
        return $doctor->appointments()
            ->with(['patient', 'appointmentType'])
            ->where('status', AppointmentStatus::Pending)
            ->orderBy('date')
            ->orderBy('start_time')
            ->limit($limit)
            ->get();
    }

    /**
     * @return \Illuminate\Support\Collection<int, Appointment>
     */
    public function todayForDoctor(User $doctor)
    {
        return $doctor->appointments()
            ->with(['patient', 'appointmentType'])
            ->whereDate('date', today())
            ->whereNot('status', AppointmentStatus::Cancelled)
            ->orderBy('start_time')
            ->get();
    }

    /**
     * @return \Illuminate\Support\Collection<int, Appointment>
     */
    public function confirmedForDoctor(User $doctor, int $limit = 10)
    {
        return $doctor->appointments()
            ->with(['patient', 'appointmentType'])
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
     * @return \Illuminate\Support\Collection<int, Appointment>
     */
    public function upcomingForDoctor(User $doctor, int $limit = 10)
    {
        return $doctor->appointments()
            ->with(['patient', 'appointmentType'])
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
    public function dashboardStats(User $doctor): array
    {
        $weekStart = now()->startOfWeek(Carbon::SATURDAY);
        $weekEnd = now()->endOfWeek(Carbon::FRIDAY);

        return [
            'today_count' => $doctor->appointments()
                ->whereDate('date', today())
                ->whereNot('status', AppointmentStatus::Cancelled)
                ->count(),
            'week_count' => $doctor->appointments()
                ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->whereNot('status', AppointmentStatus::Cancelled)
                ->count(),
            'available_slots' => $this->schedule->availableSlots($doctor, now())->count(),
            'new_patients' => $doctor->patients()
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count(),
            'pending_count' => $doctor->appointments()
                ->where('status', AppointmentStatus::Pending)
                ->count(),
            'confirmed_count' => $doctor->appointments()
                ->where('status', AppointmentStatus::Confirmed)
                ->whereDate('date', '>=', today())
                ->count(),
            'upcoming_count' => $doctor->appointments()
                ->whereDate('date', '>', today())
                ->whereIn('status', [
                    AppointmentStatus::Pending->value,
                    AppointmentStatus::Confirmed->value,
                ])
                ->count(),
            'by_type' => $this->bookingsGroupedByType($doctor),
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
