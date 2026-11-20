# Run the demo site in one container (Apache + PHP 8.3, SQLite in /data).
#   docker build -f docker/Dockerfile -t comune-demo .     (from drupal10/)
#   docker run -p 8080:80 -v comune-data:/data comune-demo
# The first start installs Drupal from config/sync with the demo content.
FROM php:8.3-apache-bookworm
RUN apt-get update \
 && apt-get install -y --no-install-recommends git unzip libpng-dev libjpeg62-turbo-dev libfreetype6-dev libzip-dev nodejs npm sqlite3 \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" gd zip opcache \
 && a2enmod rewrite headers \
 && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV APACHE_DOCUMENT_ROOT=/app/web COMPOSER_ALLOW_SUPERUSER=1
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
 && printf 'opcache.memory_consumption=128\nmemory_limit=256M\n' > /usr/local/etc/php/conf.d/drupal.ini
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --no-autoloader
COPY . .
RUN composer install --no-dev --no-interaction --no-progress \
 && (cd web/themes/custom/comune_theme && npm ci --no-audit --no-fund) \
 && mkdir -p web/sites/default/files /data \
 && chown -R www-data:www-data web/sites/default/files /data
COPY docker/entrypoint.sh /usr/local/bin/comune-entrypoint
VOLUME ["/data", "/app/web/sites/default/files"]
ENTRYPOINT ["comune-entrypoint"]
CMD ["apache2-foreground"]
