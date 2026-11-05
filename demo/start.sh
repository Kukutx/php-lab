#!/usr/bin/env bash
# Starts the four demo sites with PHP's built-in web server (development only).
set -euo pipefail
cd "$(dirname "$0")/.."
source demo/urls.sh
mkdir -p /tmp/php-lab

ROOT=$PWD
start() { # port, docroot, router (optional, relative to the repo root)
  pkill -f "php -S 0.0.0.0:$1" 2>/dev/null || true
  # Run from the docroot: routers like Laravel's server.php use getcwd().
  (cd "$ROOT/$2" && nohup php -S "0.0.0.0:$1" -t . ${3:+"$ROOT/$3"} > "/tmp/php-lab/$1.log" 2>&1 &)
}

start 8001 pdo-crud/public
start 8002 laravel/public laravel/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
start 8003 drupal10/web drupal10/web/.ht.router.php
start 8004 wordpress

cat <<TXT

php-lab demo is running:
  pdo-crud   $(url_for 8001)/contacts
  laravel    $(url_for 8002)/notices
  drupal10   $(url_for 8003)/          (admin / admin)
  wordpress  $(url_for 8004)/          (admin / admin, /wp-admin)
Logs: /tmp/php-lab/<port>.log
TXT
