#!/usr/bin/env bash
# One-time setup for the online demo (GitHub Codespaces or any Linux box with
# PHP 8.3, Composer, Node and WP-CLI). Installs dependencies and creates the
# four demo sites on SQLite. Safe to run again: it rebuilds the demo data.
set -euo pipefail
cd "$(dirname "$0")/.."
ROOT=$PWD
source demo/urls.sh

echo "==> pdo-crud"
(cd pdo-crud && composer install --no-interaction --no-progress && php bin/init-db.php)

echo "==> laravel"
(
  cd laravel
  composer install --no-interaction --no-progress
  [ -f .env ] || cp .env.example .env
  php artisan key:generate --force
  sed -i "s|^APP_URL=.*|APP_URL=$(url_for 8002)|" .env
  touch database/database.sqlite
  php artisan migrate:fresh --seed --force
)

echo "==> drupal10"
(
  cd drupal10
  composer install --no-interaction --no-progress
  (cd web/themes/custom/comune_theme && npm ci --no-audit --no-fund)
  mkdir -p web/sites/default/files
  cat > web/sites/default/settings.local.php <<PHP
<?php
// Demo-only settings, git-ignored.
\$databases['default']['default'] = [
  'database' => '$ROOT/drupal10/web/sites/default/files/.ht.sqlite',
  'prefix' => '',
  'driver' => 'sqlite',
  'namespace' => 'Drupal\\\\sqlite\\\\Driver\\\\Database\\\\sqlite',
  'autoload' => 'core/modules/sqlite/src/Driver/Database/sqlite/',
];
\$settings['hash_salt'] = '$(php -r 'echo bin2hex(random_bytes(32));')';
\$settings['trusted_host_patterns'] = ['.*'];
\$settings['reverse_proxy'] = TRUE;
\$settings['reverse_proxy_addresses'] = ['127.0.0.1', \$_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'];
PHP
  rm -f web/sites/default/files/.ht.sqlite
  vendor/bin/drush site:install standard --site-name="Comune di Esempio" --account-pass=admin -y
  vendor/bin/drush config:set system.site uuid "$(grep '^uuid' config/sync/system.site.yml | cut -d' ' -f2)" -y
  vendor/bin/drush entity:delete shortcut_set -y
  vendor/bin/drush config:import -y
  vendor/bin/drush php:script scripts/demo-content.php
  vendor/bin/drush cr
)

echo "==> wordpress"
(
  cd wordpress
  [ -f wp-load.php ] || wp core download --skip-content --allow-root
  mkdir -p wp-content/plugins wp-content/themes wp-content/database
  if [ ! -d wp-content/plugins/sqlite-database-integration ]; then
    curl -fsSL -o /tmp/sqlite.zip https://downloads.wordpress.org/plugin/sqlite-database-integration.zip
    unzip -q /tmp/sqlite.zip -d wp-content/plugins/
  fi
  sed "s|{SQLITE_IMPLEMENTATION_FOLDER_PATH}|$ROOT/wordpress/wp-content/plugins/sqlite-database-integration|; s|{SQLITE_PLUGIN}|sqlite-database-integration/load.php|" \
    wp-content/plugins/sqlite-database-integration/db.copy > wp-content/db.php
  cat > wp-config.php <<'PHP'
<?php
// Demo-only config (git-ignored). SQLite, no credentials.
define( 'DB_NAME', 'wordpress' );
define( 'DB_USER', '' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', '' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );
define( 'DB_ENGINE', 'sqlite' );
define( 'DB_DIR', __DIR__ . '/wp-content/database/' );
define( 'DB_FILE', '.ht.sqlite' );
$table_prefix = 'wp_';
// The Codespaces proxy terminates HTTPS.
if ( ( $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '' ) === 'https' ) {
	$_SERVER['HTTPS'] = 'on';
}
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
require_once ABSPATH . 'wp-settings.php';
PHP
  rm -f wp-content/database/.ht.sqlite
  wp core install --allow-root --url="$(url_for 8004)" --title="Comune di Esempio" \
    --admin_user=admin --admin_password=admin --admin_email=admin@example.com --skip-email
  wp theme is-installed twentytwentyone --allow-root || wp theme install twentytwentyone --allow-root
  wp theme activate comune-child --allow-root
  wp plugin activate sqlite-database-integration comune-avvisi --allow-root
  wp option update blogdescription "Sito istituzionale" --allow-root
  wp rewrite structure '/%postname%/' --hard --allow-root
  wp eval-file scripts/demo-content.php --allow-root
)

echo "Setup complete. Run: bash demo/start.sh"
