<?php

namespace App\Models;

use Database\Factories\WorkingHourFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkingHour extends Model
{
    /** @use HasFactory<WorkingHourFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'weekday',
        'is_open',
        'start_time',
        'end_time',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_open' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weekday' => 'integer',
            'is_open' => 'boolean',
        ];
    }

    /**
     * Owning doctor account (many weekdays per doctor).
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
