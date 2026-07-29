<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

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

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Confirmed => 'success',
            self::Completed => 'info',
            self::Cancelled => 'danger',
            self::NoShow => 'neutral',
        };
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::Pending, self::Confirmed], true);
    }

    public function canConfirm(): bool
    {
        return $this === self::Pending;
    }

    public function canComplete(): bool
    {
        return $this === self::Confirmed;
    }

    public function canMarkNoShow(): bool
    {
        return $this === self::Confirmed;
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::Pending, self::Confirmed], true);
    }

    public function occupiesSlot(): bool
    {
        return in_array($this, [self::Pending, self::Confirmed], true);
    }

    public function canSendWhatsApp(): bool
    {
        return $this === self::Confirmed;
    }

    public function canTransitionTo(self $status): bool
    {
        return match ($this) {
            self::Pending => in_array($status, [self::Confirmed, self::Cancelled], true),
            self::Confirmed => in_array($status, [self::Completed, self::Cancelled, self::NoShow], true),
            default => false,
        };
    }

    /**
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
}
