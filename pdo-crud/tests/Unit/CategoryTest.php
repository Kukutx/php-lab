<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Domain\Category;
use PHPUnit\Framework\TestCase;

final class CategoryTest extends TestCase
{
    public function testTryFromReturnsNullForUnknownValue(): void
    {
        self::assertNull(Category::tryFrom(self::userInput()));
        self::assertSame(Category::Work, Category::from('work'));
    }

    /** Pretend this string came from $_POST. */
    private static function userInput(): string
    {
        return 'enemy';
    }

    public function testEveryCaseHasALabel(): void
    {
        foreach (Category::cases() as $case) {
            self::assertNotSame('', $case->label());
        }
    }
}
