<?php

namespace App\ThirdParty\Ragnos\OpenApi;

/**
 * Marks the short-lived, internal context used to inspect controller metadata.
 */
final class OpenApiDiscovery
{
    private static int $depth = 0;

    public static function begin(): void
    {
        self::$depth++;
    }

    public static function end(): void
    {
        self::$depth = max(0, self::$depth - 1);
    }

    public static function isActive(): bool
    {
        return self::$depth > 0;
    }
}
