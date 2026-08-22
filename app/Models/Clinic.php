<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\ClinicFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clinic extends Model
{
    /** @use HasFactory<ClinicFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'specialty',
        'image_path',
        'is_active',
        'display_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @param  Builder<Clinic>  $query
     * @return Builder<Clinic>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<Clinic>  $query
     * @return Builder<Clinic>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    /**
     * @return HasMany<User, $this>
     */
    public function doctors(): HasMany
    {
        return $this->hasMany(User::class)->where('role', UserRole::Doctor);
    }

    /**
     * @return HasMany<User, $this>
     */
    public function activeDoctors(): HasMany
    {
        return $this->hasMany(User::class)->bookableStaff();
    }

    /**
     * @return HasMany<Appointment, $this>
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function activeDoctorCount(): int
    {
        return $this->activeDoctors()->count();
    }

    public function hasSingleActiveDoctor(): bool
    {
        return $this->activeDoctorCount() === 1;
    }

    public function soleActiveDoctor(): ?User
    {
        if (! $this->hasSingleActiveDoctor()) {
            return null;
        }

        return $this->activeDoctors()->first();
    }
}
