# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# app: PHP-FPM running the application
# ---------------------------------------------------------------------------

# PHP 8.4 exactly: Pdo\Mysql needs >= 8.4 and kreait/firebase-php supports <= 8.4.
FROM php:8.4-fpm AS app-base

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions bcmath exif gd gmp intl opcache pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY docker/php/app.ini $PHP_INI_DIR/conf.d/zz-app.ini
COPY docker/php/fpm-pool.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/coredb-entrypoint

WORKDIR /var/www/app
ENTRYPOINT ["coredb-entrypoint"]
CMD ["php-fpm"]


# Local development: the project is bind-mounted by compose.override.yml.
FROM app-base AS app-dev
RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"


# Production: code and dependencies are baked into the image.
FROM app-base AS app-prod
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Install dependencies before copying the code so this layer is cached
# until composer.json / composer.lock change.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-autoloader

COPY . .
RUN composer dump-autoload --no-dev --optimize \
    && mkdir -p cache public_html/files/uploaded \
    && chown www-data:www-data cache public_html/files/uploaded


# ---------------------------------------------------------------------------
# web: nginx serving static files and passing PHP requests to app
# ---------------------------------------------------------------------------
FROM nginx:stable-alpine AS web

RUN apk add --no-cache openssl
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --chmod=755 docker/nginx/10-ssl-certificate.sh /docker-entrypoint.d/10-ssl-certificate.sh
# Paths must match the app container: nginx sends SCRIPT_FILENAME to php-fpm.
COPY public_html /var/www/app/public_html
