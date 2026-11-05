# Drupal 9 → 10 upgrade: the process

Drupal 9 reached end of life in November 2023. Major upgrades since Drupal 8
are *not* migrations: the site is updated in place, as long as no code uses
APIs that were removed. The work is therefore: **find deprecated code → fix
it → update Composer dependencies → run database updates.**

> This repository is already on Drupal 10.6. The steps below are the checklist
> I would follow on a real Drupal 9 site (as I did on the Maggioli project).

## 0. Prerequisites

| | Drupal 9.5 | Drupal 10 |
|---|---|---|
| PHP | 7.3 – 8.1 | **8.1+** (8.3 recommended) |
| MySQL / MariaDB | 5.7.8 / 10.3.7 | 5.7.8 / 10.3.7 |
| Drush | 10/11 | **12+** |
| Symfony | 4 | 6 |
| CKEditor | 4 | **5** |

Start from the **latest Drupal 9.5.x** (`composer update drupal/core-* -W`, `drush updb`),
with a database backup and a working local copy (DDEV).

## 1. Inventory with Upgrade Status

```bash
ddev composer require --dev drupal/upgrade_status
ddev drush en upgrade_status -y
ddev drush upgrade_status:analyze --all        # or Reports → Upgrade status
```

It reports, per project:

- **Contrib modules/themes**: is there a Drupal 10 compatible release? → update them.
- **Custom code**: deprecated API calls (it runs PHPStan + `mglaman/phpstan-drupal`).
- **Environment**: PHP version, database version, removed core modules in use.

## 2. Fix custom code with Drupal Rector

```bash
ddev composer require --dev palantirnet/drupal-rector
cp vendor/palantirnet/drupal-rector/rector.php .
ddev exec vendor/bin/rector process web/modules/custom --dry-run   # review the diff
ddev exec vendor/bin/rector process web/modules/custom
```

Typical changes (before → after):

```php
// Entity manager was split.
\Drupal::entityManager()->getStorage('node');           // ✗ removed
\Drupal::entityTypeManager()->getStorage('node');       // ✓

// File URLs.
file_create_url($uri);                                  // ✗ removed
\Drupal::service('file_url_generator')->generateAbsoluteString($uri);

// Request time.
REQUEST_TIME;                                           // ✗ deprecated
\Drupal::time()->getRequestTime();                      // ✓ (inject datetime.time)
```

Then update every custom `*.info.yml`:

```yaml
core_version_requirement: ^9.5 || ^10
```

Rector does not fix everything: also check JavaScript (`jQuery.once` →
`core/once`), Twig (`spaceless` filter, `|without`), and themes based on
**Classy/Stable**, which left core → use `stable9`/`starterkit`, or require
the contrib `classy` theme.

## 3. Removed core modules and themes

Drupal 10 removed: Aggregator, CKEditor 4, Color, HAL, Quick Edit, RDF,
Seven, Bartik, Classy, Stable… If the site uses one, either install the
contrib replacement (`drupal/color`, `drupal/seven`…) **before** the upgrade,
or uninstall it.

## 4. CKEditor 4 → CKEditor 5

1. On 9.5 enable the core `ckeditor5` module.
2. For each text format (Configuration → Text formats) switch the editor to
   CKEditor 5; the automatic upgrade path converts toolbar buttons and
   allowed HTML, and reports plugins without an equivalent.
3. Test content with embeds, tables and custom styles.
4. Uninstall `ckeditor` (v4), then `drush cex`.

## 5. Composer update

```bash
# Allow the new major versions in composer.json
ddev composer require drupal/core-recommended:^10 drupal/core-composer-scaffold:^10 \
  drupal/core-project-message:^10 --update-with-all-dependencies --no-update
ddev composer require --dev drupal/core-dev:^10 --no-update
ddev composer require drush/drush:^12 --no-update
ddev composer update -W
```

If Composer refuses, `composer why-not drupal/core 10.0.0` shows which package
blocks it (usually a contrib module that needs a newer major version).

## 6. Database updates and config

```bash
ddev drush updb -y     # runs hook_update_N() and post-update functions
ddev drush cr
ddev drush cex -y      # updated config goes to config/sync → commit
```

On staging/production the deploy is then:

```bash
composer install --no-dev
drush updb -y && drush cim -y && drush cr
```

## 7. Test

- Upgrade Status shows no errors; `drush watchdog:show` is clean.
- Smoke test the main pages, forms, editors, Views, permissions.
- Check the front-end theme (libraries, Twig deprecations) and accessibility.

## The drush commands to remember

| Command | What it does |
|---|---|
| `drush cr` | Rebuild all caches (after code/template changes) |
| `drush updb` | Run pending database updates |
| `drush cex` | Export active config to `config/sync` |
| `drush cim` | Import config from `config/sync` into the database |
| `drush cst` | Show differences between DB and `config/sync` |
| `drush uli` | One-time admin login link |
| `drush pm:list --status=enabled` | Enabled modules |
