<?php
/** @var list<App\Domain\Contact> $contacts */
/** @var ?string $search */
/** @var ?string $flash */
require_once __DIR__ . '/helpers.php';
?>
<?php if ($flash): ?>
    <div class="alert alert-success"><?= e($flash) ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Contacts <span class="badge bg-secondary"><?= count($contacts) ?></span></h1>
    <a href="/contacts/new" class="btn btn-primary">+ New contact</a>
</div>

<form class="mb-3" method="get" action="/contacts">
    <div class="input-group">
        <input class="form-control" name="q" placeholder="Search by name or email" value="<?= e($search) ?>">
        <button class="btn btn-outline-secondary">Search</button>
    </div>
</form>

<table class="table table-hover bg-white shadow-sm">
    <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Category</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($contacts as $c): ?>
        <tr>
            <td><?= e($c->name) ?></td>
            <td><?= e($c->email) ?></td>
            <td><?= e($c->phone ?? '—') ?></td>
            <td><span class="badge bg-<?= e($c->category->badge()) ?>"><?= e($c->category->label()) ?></span></td>
            <td class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="/contacts/<?= $c->id ?>/edit">Edit</a>
                <form class="d-inline" method="post" action="/contacts/<?= $c->id ?>/delete" onsubmit="return confirm('Delete this contact?')">
                    <input type="hidden" name="_csrf" value="<?= e(App\Http\Csrf::token()) ?>">
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if ($contacts === []): ?>
        <tr><td colspan="5" class="text-center text-muted py-4">No contacts found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
