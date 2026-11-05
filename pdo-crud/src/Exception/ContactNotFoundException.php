<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

final class ContactNotFoundException extends RuntimeException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Contact #%d not found.', $id));
    }
}
