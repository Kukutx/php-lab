FROM mcr.microsoft.com/devcontainers/php:1-8.3-bookworm

# Extensions the exercises need on top of the base image:
# gd (Drupal core), zip (Composer), intl (Laravel/Drupal). pdo_sqlite is
# already included. The base image ships a Yarn apt repository whose signing
# key has expired, which makes `apt-get update` fail: we do not need it.
RUN rm -f /etc/apt/sources.list.d/yarn.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev libzip-dev libicu-dev sqlite3 unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd zip intl \
    && rm -rf /var/lib/apt/lists/*

# WP-CLI for the WordPress demo.
RUN curl -fsSL -o /usr/local/bin/wp https://github.com/wp-cli/wp-cli/releases/download/v2.12.0/wp-cli-2.12.0.phar \
    && chmod +x /usr/local/bin/wp
