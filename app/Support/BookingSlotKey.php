<?php

namespace App\Support;

use App\Support\TimeFormat;
use Carbon\CarbonInterface;

/**
 * Stable unique key for an occupied appointment slot (date + start time).
 */
class BookingSlotKey
{
    public static function for(CarbonInterface|string $date, string $startTime): string
    {
        $dateString = $date instanceof CarbonInterface
            ? $date->toDateString()
            : (string) $date;

        return $dateString.'|'.TimeFormat::normalize($startTime);
    }
}
