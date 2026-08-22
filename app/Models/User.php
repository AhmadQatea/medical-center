<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isDoctor(): bool
    {
        return $this->role === UserRole::Doctor;
    }

    public function isActiveDoctor(): bool
    {
        return $this->isDoctor() && $this->is_active;
    }

    public function isBookableStaff(): bool
    {
        return $this->is_active
            && $this->clinic_id !== null
            && ($this->isDoctor() || $this->isAdmin());
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeBookableStaff(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->whereNotNull('clinic_id')
            ->whereIn('role', [UserRole::Doctor, UserRole::Admin])
            ->orderBy('display_order')
            ->orderBy('name');
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeActiveDoctors(Builder $query): Builder
    {
        return $query
            ->where('role', UserRole::Doctor)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name');
    }

    /**
     * @return BelongsTo<Clinic, $this>
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
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
