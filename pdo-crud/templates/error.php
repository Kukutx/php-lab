<?php require_once __DIR__ . '/helpers.php'; ?>
<div class="alert alert-danger">
    <h1 class="h4"><?= (int) $status ?></h1>
    <p class="mb-0"><?= e($message) ?></p>
</div>
<a href="/contacts">&larr; Back to contacts</a>
