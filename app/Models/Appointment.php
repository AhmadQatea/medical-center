<?php

namespace App\Models;

use App\Enums\AppointmentSource;
use App\Enums\AppointmentStatus;
use App\Models\Concerns\BelongsToDoctorRouteBinding;
use App\Support\BookingSlotKey;
use App\Support\TimeFormat;
use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use BelongsToDoctorRouteBinding, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'patient_id',
        'date',
        'start_time',
        'end_time',
        'appointment_type_id',
        'cancellation_reason',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'pending',
        'source' => 'public',
    ];

    protected static function booted(): void
    {
        static::saving(function (Appointment $appointment): void {
            if ($appointment->status->occupiesSlot()
                && $appointment->date !== null
                && $appointment->start_time !== null) {
                $appointment->slot_guard_key = BookingSlotKey::for(
                    $appointment->date,
                    TimeFormat::normalize((string) $appointment->start_time),
                );
            } else {
                $appointment->slot_guard_key = null;
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => AppointmentStatus::class,
            'source' => AppointmentSource::class,
        ];
    }

    public function typeLabel(): string
    {
        return $this->appointmentType?->name ?? '—';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Patient, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * @return BelongsTo<AppointmentType, $this>
     */
    public function appointmentType(): BelongsTo
    {
        return $this->belongsTo(AppointmentType::class);
    }
}
