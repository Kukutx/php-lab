#!/usr/bin/env bash
# Writes settings.local.php from the environment and installs the site on first start.
#   ASSISTANT_SERVER, ASSISTANT_WIDGET_ID  optional: show the site assistant (see ../README.md)
#   TRUSTED_HOST                          optional: host name regex, default any
set -euo pipefail
cd /app
[ -f /data/salt ] || head -c 32 /dev/urandom | od -An -tx1 | tr -d ' \n' > /data/salt
cat > web/sites/default/settings.local.php <<PHP
<?php
\$databases['default']['default'] = [
  'database' => '/data/site.sqlite',
  'prefix' => '',
  'driver' => 'sqlite',
  'namespace' => 'Drupal\\\\sqlite\\\\Driver\\\\Database\\\\sqlite',
  'autoload' => 'core/modules/sqlite/src/Driver/Database/sqlite/',
];
\$settings['hash_salt'] = '$(cat /data/salt)';
\$settings['trusted_host_patterns'] = ['${TRUSTED_HOST:-.*}'];
\$settings['reverse_proxy'] = TRUE;
\$settings['reverse_proxy_addresses'] = [\$_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'];
PHP
if [ -n "${ASSISTANT_SERVER:-}" ] && [ -n "${ASSISTANT_WIDGET_ID:-}" ]; then
  printf "\$settings['embed_assistant'] = ['server' => '%s', 'widget_id' => '%s'];\n" "$ASSISTANT_SERVER" "$ASSISTANT_WIDGET_ID" >> web/sites/default/settings.local.php
fi
drush() { su -s /bin/bash www-data -c "vendor/bin/drush $*"; }
if [ ! -f /data/site.sqlite ]; then
  chown -R www-data:www-data /data web/sites/default/files
  pass=$(head -c 18 /dev/urandom | base64 | tr -dc 'A-Za-z0-9')
  drush site:install standard --site-name="'Comune di Esempio'" --account-pass="$pass" -y
  echo "$pass" > /data/admin-password && chmod 600 /data/admin-password
  drush config:set system.site uuid "$(grep '^uuid' config/sync/system.site.yml | cut -d' ' -f2)" -y
  drush entity:delete shortcut_set -y
  drush config:import -y
  drush php:script scripts/demo-content.php
fi
drush cr >/dev/null
exec docker-php-entrypoint "$@"
