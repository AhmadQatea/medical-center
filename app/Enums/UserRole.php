<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Doctor = 'doctor';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'مدير المركز',
            self::Doctor => 'طبيب',
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }

    public function isDoctor(): bool
    {
        return $this === self::Doctor;
    }
}
