<?php

declare(strict_types=1);

/**
 * Escape output for HTML. Every value printed in a template goes through e(),
 * which is how we prevent XSS: "<script>" becomes "&lt;script&gt;".
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
