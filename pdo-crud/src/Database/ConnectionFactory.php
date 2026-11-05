<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

/**
 * Builds the PDO connection from environment variables, so no credentials
 * live in the code. Defaults to a local SQLite file; inside DDEV the
 * .ddev/config.yaml sets DB_DSN to the MariaDB container.
 */
final class ConnectionFactory
{
    public static function fromEnvironment(string $projectRoot): PDO
    {
        $dsn = getenv('DB_DSN') ?: 'sqlite:' . $projectRoot . '/var/contacts.sqlite';
        $user = getenv('DB_USER') ?: null;
        $pass = getenv('DB_PASSWORD') ?: null;

        return self::create($dsn, $user, $pass);
    }

    public static function create(string $dsn, ?string $user = null, ?string $pass = null): PDO
    {
        return new PDO($dsn, $user, $pass, [
            // Throw PDOException on errors instead of silently returning false.
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Use real server-side prepared statements on MySQL.
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    /** Creates the table if missing. Portable between SQLite and MySQL/MariaDB. */
    public static function migrate(PDO $pdo): void
    {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $idColumn = match ($driver) {
            'sqlite' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
            'mysql' => 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY',
            default => throw new \RuntimeException("Unsupported driver: {$driver}"),
        };

        $pdo->exec("CREATE TABLE IF NOT EXISTS contacts (
            id {$idColumn},
            name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(20) NULL,
            category VARCHAR(20) NOT NULL DEFAULT 'personal',
            created_at DATETIME NOT NULL
        )");
    }
}
