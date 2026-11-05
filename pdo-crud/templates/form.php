<?php
/** @var App\Domain\Contact $contact */
/** @var array<string, string> $errors */
/** @var list<App\Domain\Category> $categories */
require_once __DIR__ . '/helpers.php';
$isNew = $contact->id === null;
$action = $isNew ? '/contacts' : '/contacts/' . $contact->id;
$invalid = fn (string $field): string => isset($errors[$field]) ? ' is-invalid' : '';
?>
<h1 class="h3 mb-3"><?= $isNew ? 'New contact' : 'Edit contact' ?></h1>

<form method="post" action="<?= e($action) ?>" class="card card-body shadow-sm" novalidate>
    <input type="hidden" name="_csrf" value="<?= e(App\Http\Csrf::token()) ?>">

    <div class="mb-3">
        <label class="form-label" for="name">Name *</label>
        <input class="form-control<?= $invalid('name') ?>" id="name" name="name" value="<?= e($contact->name) ?>">
        <div class="invalid-feedback"><?= e($errors['name'] ?? '') ?></div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="email">Email *</label>
        <input class="form-control<?= $invalid('email') ?>" id="email" name="email" type="email" value="<?= e($contact->email) ?>">
        <div class="invalid-feedback"><?= e($errors['email'] ?? '') ?></div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="phone">Phone</label>
        <input class="form-control<?= $invalid('phone') ?>" id="phone" name="phone" value="<?= e($contact->phone) ?>">
        <div class="invalid-feedback"><?= e($errors['phone'] ?? '') ?></div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="category">Category</label>
        <select class="form-select<?= $invalid('category') ?>" id="category" name="category">
            <?php foreach ($categories as $cat): ?>
                <option value="<?= e($cat->value) ?>" <?= $cat === $contact->category ? 'selected' : '' ?>><?= e($cat->label()) ?></option>
            <?php endforeach; ?>
        </select>
        <div class="invalid-feedback"><?= e($errors['category'] ?? '') ?></div>
    </div>
    <div>
        <button class="btn btn-primary">Save</button>
        <a href="/contacts" class="btn btn-link">Cancel</a>
    </div>
</form>
