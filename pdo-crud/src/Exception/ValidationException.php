<?php

declare(strict_types=1);

namespace App\Exception;

use InvalidArgumentException;

/**
 * Carries all field errors at once, so the form can show every problem
 * instead of failing on the first one.
 */
final class ValidationException extends InvalidArgumentException
{
    /** @param array<string, string> $errors field => message */
    public function __construct(public readonly array $errors)
    {
        parent::__construct('The submitted data is not valid.');
    }
}
