<?php

declare(strict_types=1);

use App\Database\ConnectionFactory;
use App\Http\ContactController;
use App\Http\View;
use App\Repository\PdoContactRepository;
use App\Validation\ContactValidator;

$root = dirname(__DIR__);

// Composer's autoloader maps the App\ namespace to src/ (PSR-4),
// so we never write require for our own classes.
require $root . '/vendor/autoload.php';

// Hardened session cookie: not readable from JavaScript, not sent cross-site.
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_secure' => ($_SERVER['HTTPS'] ?? '') === 'on',
]);

$pdo = ConnectionFactory::fromEnvironment($root);
ConnectionFactory::migrate($pdo);

$controller = new ContactController(
    new PdoContactRepository($pdo),
    new ContactValidator(),
    new View($root . '/templates'),
);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$controller->handle($_SERVER['REQUEST_METHOD'] ?? 'GET', rtrim($path, '/') ?: '/');
