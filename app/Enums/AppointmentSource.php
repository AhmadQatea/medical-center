<?php

namespace App\Enums;

enum AppointmentSource: string
{
    case Instant = 'instant';
    case Public = 'public';
    case Whatsapp = 'whatsapp';

    public function label(): string
    {
        return match ($this) {
            self::Instant => 'حجز فوري',
            self::Public => 'حجز عام',
            self::Whatsapp => 'واتساب',
        };
    }
}
