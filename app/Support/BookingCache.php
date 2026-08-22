<?php

namespace App\Support;

/**
 * Cache keys for safe, short-lived public/admin reference data.
 * Never used for appointment availability or booking correctness.
 */
final class BookingCache
{
    public const ACTIVE_CLINICS_TTL = 120;

    public const CLINIC_SETTINGS_TTL = 300;

    public static function activeClinicsKey(): string
    {
        return 'booking.active_clinics.v1';
    }

    public static function clinicSettingsKey(int $userId): string
    {
        return "clinic_settings.user.{$userId}.v1";
    }

    public static function forgetActiveClinics(): void
    {
        cache()->forget(self::activeClinicsKey());
    }

    public static function forgetClinicSettings(int $userId): void
    {
        cache()->forget(self::clinicSettingsKey($userId));
    }
}
