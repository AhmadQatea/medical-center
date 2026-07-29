<?php

namespace App\Enums;

/**
 * Lifecycle status of a clinic appointment / booking.
 *
 * Used by the doctor dashboard, bookings list, timeline, and public booking flow
 * to communicate whether a slot is waiting, active, finished, or void.
 */
enum BookingStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    /**
     * Arabic label for UI badges and filters.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'قيد الانتظار',
            self::Confirmed => 'مؤكد',
            self::Completed => 'مكتمل',
            self::Cancelled => 'ملغي',
            self::NoShow => 'لم يحضر',
        };
    }

    /**
     * Design-system color token (maps to badge / alert variants).
     */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Confirmed => 'primary',
            self::Completed => 'success',
            self::Cancelled => 'danger',
            self::NoShow => 'secondary',
        };
    }

    /**
     * Heroicon name for status indicators.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'clock',
            self::Confirmed => 'check-circle',
            self::Completed => 'check-badge',
            self::Cancelled => 'x-circle',
            self::NoShow => 'user-minus',
        };
    }

    /**
     * Options list for Blade &lt;select&gt; components (value => Arabic label).
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /**
     * Resolve a backed value to the enum case.
     *
     * @throws \ValueError
     */
    public static function fromValue(string $value): self
    {
        return self::tryFrom($value)
            ?? throw new \ValueError("Invalid BookingStatus value [{$value}].");
    }
}
