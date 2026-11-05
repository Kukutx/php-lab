<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Contact;
use App\Exception\ContactNotFoundException;

/**
 * The controller depends on this interface, not on PDO. That makes it easy
 * to swap the storage (or use a fake in tests) without touching the controller.
 */
interface ContactRepository
{
    /** @return list<Contact> */
    public function all(?string $search = null): array;

    /** @throws ContactNotFoundException */
    public function find(int $id): Contact;

    public function save(Contact $contact): Contact;

    /** @throws ContactNotFoundException */
    public function delete(int $id): void;
}
