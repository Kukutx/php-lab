# How php-lab was built and checked

[中文](../zh/how-i-built-it.md) · **English** · [Italiano](../it/how-i-built-it.md)

This note is about method: how the projects were made easy to run, how quality is checked, and how problems are fixed once found. It doesn't go through the code line by line.

## 1. Every project starts with one command

- Every sub-project uses **SQLite**, so there's no MySQL to install and no password to keep in the repository.
- `demo/setup.sh` installs all four sites from scratch (composer install, database, demo data), and `demo/start.sh` starts them with PHP's built-in server on ports 8001–8004.
- `.devcontainer/` makes GitHub Codespaces run both scripts automatically, so a single click is enough to see everything online.
- Why: if a project takes ten manual steps to start, other people (including me, later on) won't run it. With the installation written as a script, the instructions can't drift away from what actually works.

## 2. Check what a real user does, not only the code

I wrote a small **Playwright** (browser automation) script that uses each site the way a person would:

1. Open the home page and the list pages, and use the filters and pagination.
2. Log in as admin/admin, then create, edit and delete an item.
3. Resize the window to a 390px phone width and go through everything again.
4. Throughout, record every HTTP response with a status of 400 or above, browser console errors, and whether the page overflows horizontally (`scrollWidth > 390`), and take screenshots.

Problems found and fixed in this round:

| Site | Problem | Cause | Fix |
| --- | --- | --- | --- |
| pdo-crud | Table wider than the screen on phones | Too many columns | Wrapped in `.table-responsive`; Phone column hidden on phones; buttons don't wrap |
| laravel | A notice "disappeared" when its date was left empty | An empty date means a draft; sorted newest first, NULL dates ended up on the last page; drafts also showed in the public list | The create form defaults to "now"; the list shows only published notices by default, with a "Drafts & scheduled / All" filter; added a test |
| laravel | Navbar subtitle cut off on phones | Not enough room | Subtitle hidden on small screens (`d-none d-sm-inline`) |
| drupal10 | Hamburger menu icon returned 404 on admin pages | The template used a relative path, which became `/user/themes/...` under `/user/1` | Always use the absolute path `{{ sprites }}` built in preprocess |
| drupal10 | No way to log in | No account link in the header | Following the "modello Comuni", added "Accedi all'area personale" to the slim header; after login it becomes "Area personale / Esci" |
| drupal10 | Avvisi came before Home in the menu; the filter showed the English "- Any -" | Both menu items had weight 0; Views default label | Adjusted the weights and exported them to `config/sync` with `drush cex`; changed the label in `hook_form_views_exposed_form_alter` |
| wordpress | No navigation menu and no login link | The demo script didn't create a menu | The demo script now creates Home / Avvisi / Servizi / Accedi with `wp_create_nav_menu` and assigns it to the `primary` location |
| wordpress | Footer showed the default English widgets and "Hello world!" | WordPress sample content | Deleted the sample post and replaced the footer with a "Contatti" block |
| wordpress | Elementor wasn't installed, so the custom widget couldn't be seen | Never installed | `setup.sh` runs `wp plugin install elementor --activate` (when online) and also installs the Italian language pack |

## 3. The same routine after every change

After every change, in this order; CI does the same:

```bash
# Automated tests
cd pdo-crud && composer test        # PHPUnit
cd laravel  && php artisan test     # Laravel feature and unit tests
# Coding standards and static analysis
composer stan && composer cs        # PHPStan level 8 + PSR-12 (pdo-crud)
vendor/bin/pint --test              # Laravel
vendor/bin/phpcs                    # Drupal coder / WordPress WPCS
# Then run the Playwright check again and look at the screenshots
```

- Drupal configuration changes (menu weights, for example) must go into `config/sync`, or a reinstall loses them. After changing something in the UI, run `drush cex`; others run `drush cim` after pulling.
- WordPress data (menus, widgets) isn't in the code, so it goes into `scripts/demo-content.php`, which makes every install produce the same result.

## 4. A few habits

- **Reproduce first, then fix**: prove the problem exists with a script, then prove it's gone with the same script.
- **Fix the root cause**: the Laravel issue looked like "the notice disappeared", but the cause was drafts and sorting, so rather than only changing the sort order, the draft/published concept was completed.
- **Small commits**: one change per commit, with a message that explains why.
- **Secrets never go into the repository**: `.env`, `wp-config.php` and `settings.local.php` are in `.gitignore`, and gitleaks checks every commit in CI and in a pre-commit hook.
