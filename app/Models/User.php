<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Clinic brand, specialty, WhatsApp, and media (exactly one row).
     *
     * @return HasOne<ClinicSetting, $this>
     */
    public function clinicSetting(): HasOne
    {
        return $this->hasOne(ClinicSetting::class);
    }

    /**
     * Global schedule rules: slot duration, break, lunch (exactly one row).
     *
     * @return HasOne<ScheduleSetting, $this>
     */
    public function scheduleSetting(): HasOne
    {
        return $this->hasOne(ScheduleSetting::class);
    }

    /**
     * Per-weekday open/closed windows (typically seven rows).
     *
     * @return HasMany<WorkingHour, $this>
     */
    public function workingHours(): HasMany
    {
        return $this->hasMany(WorkingHour::class);
    }

    /**
     * Full-day clinic closures excluded from booking.
     *
     * @return HasMany<Holiday, $this>
     */
    public function holidays(): HasMany
    {
        return $this->hasMany(Holiday::class);
    }

    /**
     * Patient directory owned by this doctor (no patient auth).
     *
     * @return HasMany<Patient, $this>
     */
    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    /**
     * Appointments owned by this doctor (dashboard, bookings, timeline).
     *
     * @return HasMany<Appointment, $this>
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Configurable visit types for booking forms and reports.
     *
     * @return HasMany<AppointmentType, $this>
     */
    public function appointmentTypes(): HasMany
    {
        return $this->hasMany(AppointmentType::class);
    }
}
