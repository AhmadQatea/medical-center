<?php

namespace App\Enums;

/**
 * Calendar weekday for schedule management and working hours.
 *
 * Ordered Saturday → Friday to match common Arabic (Gulf) week presentation
 * used in the clinic schedule UI. Persist the backed string (or map to an
 * integer weekday index in a service later) — models are not updated here.
 */
enum WeekDay: string
{
    case Saturday = 'saturday';
    case Sunday = 'sunday';
    case Monday = 'monday';
    case Tuesday = 'tuesday';
    case Wednesday = 'wednesday';
    case Thursday = 'thursday';
    case Friday = 'friday';

    /**
     * Arabic weekday label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Saturday => 'السبت',
            self::Sunday => 'الأحد',
            self::Monday => 'الإثنين',
            self::Tuesday => 'الثلاثاء',
            self::Wednesday => 'الأربعاء',
            self::Thursday => 'الخميس',
            self::Friday => 'الجمعة',
        };
    }

    /**
     * Design-system color token for weekday chips in the schedule UI.
     */
    public function color(): string
    {
        return match ($this) {
            self::Friday => 'warning',
            self::Saturday, self::Sunday => 'secondary',
            self::Monday, self::Tuesday, self::Wednesday, self::Thursday => 'primary',
        };
    }

    /**
     * Heroicon name for weekday / calendar UI.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Friday => 'moon',
            self::Saturday, self::Sunday => 'calendar',
            default => 'calendar-days',
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
            ?? throw new \ValueError("Invalid WeekDay value [{$value}].");
    }
}
