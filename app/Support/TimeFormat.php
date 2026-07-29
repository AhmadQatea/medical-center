<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * 12-hour clock formatting for clinic UI (Arabic + English).
 */
class TimeFormat
{
    /**
     * Arabic 12-hour display: 09:00 صباحاً, 12:00 ظهراً, 02:30 مساءً.
     */
    public static function arabic(string $time): string
    {
        [$hour, $minute, $period] = self::parts($time);

        return sprintf('%02d:%s %s', $hour, $minute, $period);
    }

    /**
     * English 12-hour display: 09:00 AM, 02:30 PM.
     */
    public static function english(string $time): string
    {
        return Carbon::parse($time)->format('h:i A');
    }

    /**
     * @return array{0: int, 1: string, 2: string} hour (1-12), minute, Arabic period
     */
    public static function parts(string $time): array
    {
        $carbon = Carbon::parse($time);
        $hour24 = (int) $carbon->format('H');
        $minute = $carbon->format('i');

        $period = match (true) {
            $hour24 === 12 => 'ظهراً',
            $hour24 < 12 => 'صباحاً',
            default => 'مساءً',
        };

        $hour12 = $hour24 % 12;

        if ($hour12 === 0) {
            $hour12 = 12;
        }

        return [$hour12, $minute, $period];
    }

    /**
     * Normalize stored time to H:i for comparisons and slot keys.
     */
    public static function normalize(string $time): string
    {
        return Carbon::parse($time)->format('H:i');
    }
}
