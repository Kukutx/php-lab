<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Domain\Category;
use App\Exception\ValidationException;
use App\Validation\ContactValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContactValidatorTest extends TestCase
{
    private ContactValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new ContactValidator();
    }

    public function testValidInputBecomesAContact(): void
    {
        $contact = $this->validator->validate([
            'name' => '  Giulia Rossi ',
            'email' => 'giulia@example.com',
            'phone' => '',
            'category' => 'work',
        ]);

        self::assertSame('Giulia Rossi', $contact->name, 'name is trimmed');
        self::assertNull($contact->phone, 'empty phone becomes null');
        self::assertSame(Category::Work, $contact->category);
    }

    /** @return iterable<string, array{array<string, string>, string}> */
    public static function invalidInput(): iterable
    {
        $valid = ['name' => 'A', 'email' => 'a@example.com', 'phone' => '', 'category' => 'work'];

        yield 'missing name' => [['name' => ''] + $valid, 'name'];
        yield 'bad email' => [['email' => 'not-an-email'] + $valid, 'email'];
        yield 'bad phone' => [['phone' => 'call me'] + $valid, 'phone'];
        yield 'unknown category' => [['category' => 'enemy'] + $valid, 'category'];
    }

    /** @param array<string, string> $input */
    #[DataProvider('invalidInput')]
    public function testInvalidInputIsRejected(array $input, string $field): void
    {
        try {
            $this->validator->validate($input);
            self::fail('Expected ValidationException');
        } catch (ValidationException $e) {
            self::assertArrayHasKey($field, $e->errors);
        }
    }
}
