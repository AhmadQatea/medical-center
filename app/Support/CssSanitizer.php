<?php

namespace App\Support;

/**
 * Sanitize values embedded in CSS declarations to prevent injection.
 */
class CssSanitizer
{
    public static function declarationValue(string $value): string
    {
        return str_replace(["\0", "\r", "\n", '"', "'", '\\', ';', '{', '}', '<', '>'], '', $value);
    }

    public static function url(string $path): string
    {
        $path = self::declarationValue($path);

        if (str_starts_with($path, 'data:')) {
            if (! preg_match('/^data:image\/[a-zA-Z0-9+.-]+;base64,[a-zA-Z0-9+\/=]+$/', $path)) {
                return '';
            }

            return $path;
        }

        return $path;
    }
}
