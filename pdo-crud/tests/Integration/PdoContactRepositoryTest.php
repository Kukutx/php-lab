<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Database\ConnectionFactory;
use App\Domain\Category;
use App\Domain\Contact;
use App\Exception\ContactNotFoundException;
use App\Repository\PdoContactRepository;
use PHPUnit\Framework\TestCase;

/**
 * Runs the real SQL against an in-memory SQLite database:
 * fast, isolated (fresh DB per test) and needs no server.
 */
final class PdoContactRepositoryTest extends TestCase
{
    private PdoContactRepository $repo;

    protected function setUp(): void
    {
        $pdo = ConnectionFactory::create('sqlite::memory:');
        ConnectionFactory::migrate($pdo);
        $this->repo = new PdoContactRepository($pdo);
    }

    public function testCreateAndFind(): void
    {
        $saved = $this->repo->save(new Contact('Giulia', 'giulia@example.com', null, Category::Work));

        self::assertNotNull($saved->id);
        self::assertEquals($saved, $this->repo->find($saved->id));
    }

    public function testUpdate(): void
    {
        $saved = $this->repo->save(new Contact('Giulia', 'giulia@example.com'));
        $this->repo->save(new Contact('Giulia R.', 'new@example.com', '123456', Category::Family, $saved->id));

        $found = $this->repo->find((int) $saved->id);
        self::assertSame('Giulia R.', $found->name);
        self::assertSame(Category::Family, $found->category);
    }

    public function testDelete(): void
    {
        $saved = $this->repo->save(new Contact('Giulia', 'giulia@example.com'));
        $this->repo->delete((int) $saved->id);

        $this->expectException(ContactNotFoundException::class);
        $this->repo->find((int) $saved->id);
    }

    public function testSearchIsSafeAgainstSqlInjection(): void
    {
        $this->repo->save(new Contact('Giulia', 'giulia@example.com'));
        $this->repo->save(new Contact('Marco', 'marco@example.com'));

        self::assertCount(1, $this->repo->all('giu'));
        // A classic injection payload is treated as plain text, so it matches nothing.
        self::assertCount(0, $this->repo->all("' OR '1'='1"));
    }

    public function testDeletingMissingContactThrows(): void
    {
        $this->expectException(ContactNotFoundException::class);
        $this->repo->delete(999);
    }
}
