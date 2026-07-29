<?php

namespace App\Models;

use Database\Factories\ClinicSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicSetting extends Model
{
    /** @use HasFactory<ClinicSettingFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'clinic_name',
        'specialty',
        'description',
        'city',
        'address',
        'whatsapp_number',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'specialty' => 'طبيب أسنان',
    ];

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
