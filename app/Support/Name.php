<?php

namespace App\Support;

class Name
{
    /**
     * Build short initials from an Arabic or Latin display name.
     */
    public static function initials(string $name, int $max = 2): string
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        $parts = array_values(array_filter($parts, fn (string $part): bool => $part !== ''));

        if ($parts === []) {
            return '؟';
        }

        $letters = array_map(
            fn (string $part): string => mb_substr($part, 0, 1),
            array_slice($parts, 0, $max),
        );

        return implode('', $letters);
    }
}
