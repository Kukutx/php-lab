<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Contact;
use App\Exception\ContactNotFoundException;
use PDO;

/**
 * Every query uses prepared statements with bound parameters.
 * The SQL text and the user data travel separately to the database,
 * so user input can never change the structure of the query (no SQL injection).
 */
final class PdoContactRepository implements ContactRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function all(?string $search = null): array
    {
        if ($search === null || $search === '') {
            $stmt = $this->pdo->query('SELECT * FROM contacts ORDER BY name');
        } else {
            // The wildcard is part of the *value*, never concatenated into the SQL.
            $stmt = $this->pdo->prepare(
                'SELECT * FROM contacts WHERE name LIKE :q OR email LIKE :q ORDER BY name'
            );
            $stmt->execute(['q' => '%' . $search . '%']);
        }

        $contacts = [];
        foreach ($stmt->fetchAll() as $row) {
            $contacts[] = Contact::fromRow($row);
        }

        return $contacts;
    }

    public function find(int $id): Contact
    {
        $stmt = $this->pdo->prepare('SELECT * FROM contacts WHERE id = :id');
        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();
        if ($row === false) {
            throw ContactNotFoundException::withId($id);
        }

        return Contact::fromRow($row);
    }

    public function save(Contact $contact): Contact
    {
        $params = [
            'name' => $contact->name,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'category' => $contact->category->value,
        ];

        if ($contact->id === null) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO contacts (name, email, phone, category, created_at)
                 VALUES (:name, :email, :phone, :category, :created_at)'
            );
            $stmt->execute($params + ['created_at' => date('Y-m-d H:i:s')]);

            return $this->find((int) $this->pdo->lastInsertId());
        }

        $this->find($contact->id); // throws if it does not exist

        $stmt = $this->pdo->prepare(
            'UPDATE contacts SET name = :name, email = :email, phone = :phone, category = :category
             WHERE id = :id'
        );
        $stmt->execute($params + ['id' => $contact->id]);

        return $this->find($contact->id);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM contacts WHERE id = :id');
        $stmt->execute(['id' => $id]);

        if ($stmt->rowCount() === 0) {
            throw ContactNotFoundException::withId($id);
        }
    }
}
