<?php

declare(strict_types=1);

namespace App\Domain;

use DateTimeImmutable;

/**
 * Immutable value object. "readonly class" (PHP 8.2) makes every promoted
 * property write-once, so a Contact cannot be modified by accident after it
 * is created. To "change" a contact we build a new one (see withId()).
 */
final readonly class Contact
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone = null,
        public Category $category = Category::Personal,
        public ?int $id = null,
        public ?DateTimeImmutable $createdAt = null,
    ) {
    }

    public function withId(int $id): self
    {
        return new self($this->name, $this->email, $this->phone, $this->category, $id, $this->createdAt);
    }

    /**
     * Named constructor used by the repository to hydrate a DB row.
     *
     * @param array{
     *     id: int|string, name: string, email: string,
     *     phone: ?string, category: string, created_at: string
     * } $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            name: $row['name'],
            email: $row['email'],
            phone: $row['phone'],
            category: Category::from($row['category']),
            id: (int) $row['id'],
            createdAt: new DateTimeImmutable($row['created_at']),
        );
    }
}
