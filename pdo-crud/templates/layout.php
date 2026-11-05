<?php require_once __DIR__ . '/helpers.php'; ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contacts · pdo-crud (php-lab)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="/contacts">📇 Contacts <small class="text-secondary">plain PHP 8 + PDO</small></a>
    </div>
</nav>
<main class="container">
    <?= $content ?>
</main>
</body>
</html>
