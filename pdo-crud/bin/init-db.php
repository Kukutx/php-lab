<?php

declare(strict_types=1);

use App\Database\ConnectionFactory;
use App\Domain\Category;
use App\Domain\Contact;
use App\Repository\PdoContactRepository;

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

@mkdir($root . '/var');
$pdo = ConnectionFactory::fromEnvironment($root);
ConnectionFactory::migrate($pdo);
$pdo->exec('DELETE FROM contacts');

$repo = new PdoContactRepository($pdo);
$seed = [
    new Contact('Giulia Rossi', 'giulia.rossi@example.com', '+39 333 123 4567', Category::Work),
    new Contact('Marco Bianchi', 'marco@example.com', null, Category::Personal),
    new Contact('Anna Verdi', 'anna.verdi@example.com', '+39 02 1234 5678', Category::Family),
    new Contact('Luca Esposito', 'luca.esposito@example.org', '+39 347 987 6543', Category::Work),
];
foreach ($seed as $contact) {
    $repo->save($contact);
}

echo 'Database ready with ' . count($seed) . " demo contacts.\n";
