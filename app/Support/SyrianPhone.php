<?php

namespace App\Support;

/**
 * Normalize Syrian mobile numbers to +9639xxxxxxxx for booking forms.
 */
class SyrianPhone
{
    public static function normalize(?string $number): ?string
    {
        if ($number === null) {
            return null;
        }

        $value = trim($number);

        if ($value === '') {
            return '';
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';

        while (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '963') && strlen($digits) === 12) {
            return '+'.$digits;
        }

        if (str_starts_with($digits, '09') && strlen($digits) === 10) {
            return '+963'.substr($digits, 1);
        }

        if (str_starts_with($digits, '9') && strlen($digits) === 9) {
            return '+963'.$digits;
        }

        $compact = preg_replace('/\s+/', '', $value) ?? $value;

        if (str_starts_with($compact, '+963') && preg_match('/^\+9639\d{8}$/', $compact)) {
            return $compact;
        }

        return $compact;
    }

    public static function isValid(?string $number): bool
    {
        $normalized = self::normalize($number);

        return is_string($normalized) && preg_match('/^\+9639\d{8}$/', $normalized) === 1;
    }
}
