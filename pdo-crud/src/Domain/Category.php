<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * Backed enum: the string value is what we store in the database,
 * so invalid categories cannot exist in PHP code (tryFrom() returns null).
 */
enum Category: string
{
    case Personal = 'personal';
    case Work = 'work';
    case Family = 'family';

    /** Human readable label, using match (strict comparison, must be exhaustive). */
    public function label(): string
    {
        return match ($this) {
            self::Personal => 'Personal',
            self::Work => 'Work',
            self::Family => 'Family',
        };
    }

    /** Bootstrap badge colour for the UI. */
    public function badge(): string
    {
        return match ($this) {
            self::Personal => 'primary',
            self::Work => 'success',
            self::Family => 'warning',
        };
    }
}
