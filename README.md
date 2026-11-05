# php-lab

[![CI](https://github.com/Kukutx/php-lab/actions/workflows/ci.yml/badge.svg)](https://github.com/Kukutx/php-lab/actions/workflows/ci.yml)

A monorepo of small, focused PHP exercises. Each folder is a standalone
project with its own Composer setup and its own [DDEV](https://ddev.com)
environment, so you can start any of them independently.

| Folder | What it covers | Status |
|---|---|---|
| [`pdo-crud/`](pdo-crud) | Plain PHP 8 contacts CRUD: PDO prepared statements, Composer + PSR-4, enums, readonly classes, `match`, exceptions, CSRF/XSS protection, PHPUnit | ✅ |
| [`laravel/`](laravel) | Laravel tour: routes, controllers, Eloquent + migrations, form requests / validation, Blade, feature tests | ✅ |
| [`notes/`](notes) | Interview cheat sheet (Chinese notes + one-line Italian answers) | ✅ |
| [`drupal10/`](drupal10) | Municipal site: content type, taxonomy, Views, blocks, custom module (routing, controller, services/DI, config form, block plugin), Bootstrap Italia theme ("modello Comuni" home + Avvisi list), config management, 9→10 upgrade guide, WCAG notes | ✅ |
| [`wordpress/`](wordpress) | Child theme (hooks, template hierarchy), plugin with custom post type, taxonomy, secure meta box, shortcode, REST meta and an Elementor widget; Elementor notes | ✅ |

## Try it online (GitHub Codespaces)

[![Open in GitHub Codespaces](https://github.com/codespaces/badge.svg)](https://codespaces.new/Kukutx/php-lab?quickstart=1)

1. Click the badge (or **Code → Codespaces → Create codespace on main**).
2. Wait for the first setup (≈5–10 min): `demo/setup.sh` installs Composer/npm
   dependencies and creates the four sites on SQLite with demo content.
3. Open the **Ports** tab and click the globe icon next to a port:

| Port | Site | Login |
|---|---|---|
| 8001 | pdo-crud → `/contacts` | – |
| 8002 | laravel → `/notices` | – |
| 8003 | drupal10 | `admin` / `admin` |
| 8004 | wordpress (`/wp-admin`) | `admin` / `admin` |

Servers start again automatically when the codespace resumes
(`bash demo/start.sh`); rebuild all demo data with `bash demo/setup.sh`.
The demo uses PHP's built-in web server: fine for development, never for production.

## Quick start

```bash
git clone https://github.com/Kukutx/php-lab.git
cd php-lab/pdo-crud     # or laravel/, drupal10/, wordpress/
ddev start              # one DDEV project per folder
ddev composer install
ddev launch
```

Each folder's README also explains how to run it **without** DDEV
(PHP 8.2+ and Composer are enough; SQLite is used by default).

## Repository rules

- **No secrets in git.** `.env` files are ignored everywhere; only
  `.env.example` is committed. A [gitleaks](https://github.com/gitleaks/gitleaks)
  pre-commit hook scans every commit:
  ```bash
  pip install pre-commit && pre-commit install
  ```
- Only own code is committed: no `vendor/`, no framework core, no uploaded files,
  no database dumps.
- Code, comments, commits and READMEs are in English.

## License

[MIT](LICENSE)
