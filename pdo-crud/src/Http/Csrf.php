<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Synchronizer token pattern: a random token is stored in the session and
 * embedded in every form. A forged request from another site cannot read
 * our page, so it cannot know the token.
 */
final class Csrf
{
    private const KEY = '_csrf';

    public static function token(): string
    {
        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::KEY];
    }

    public static function isValid(?string $token): bool
    {
        // hash_equals() compares in constant time (no timing attacks).
        return is_string($token) && isset($_SESSION[self::KEY]) && hash_equals($_SESSION[self::KEY], $token);
    }
}
