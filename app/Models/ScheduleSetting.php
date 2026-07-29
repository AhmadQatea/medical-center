<?php

namespace App\Models;

use Database\Factories\ScheduleSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleSetting extends Model
{
    /** @use HasFactory<ScheduleSettingFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'appointment_duration_minutes',
        'break_duration_minutes',
        'lunch_enabled',
        'lunch_start',
        'lunch_end',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'appointment_duration_minutes' => 30,
        'break_duration_minutes' => 0,
        'lunch_enabled' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'appointment_duration_minutes' => 'integer',
            'break_duration_minutes' => 'integer',
            'lunch_enabled' => 'boolean',
        ];
    }

    /**
     * Owning doctor account (1:1 with users).
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
