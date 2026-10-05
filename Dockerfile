# syntax=docker/dockerfile:1

# PHP 8.4 exactly: Pdo\Mysql needs >= 8.4 and kreait/firebase-php supports <= 8.4.
FROM php:8.4-apache AS base

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions bcmath exif gd gmp intl opcache pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN a2enmod rewrite \
    && echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername
COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php/app.ini $PHP_INI_DIR/conf.d/zz-app.ini
COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/coredb-entrypoint

WORKDIR /var/www/app
ENTRYPOINT ["coredb-entrypoint"]
CMD ["apache2-foreground"]


# Local development: the project is bind-mounted by compose.override.yml.
FROM base AS dev
RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"


# Production: code and dependencies are baked into the image.
FROM base AS prod
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Install dependencies before copying the code so this layer is cached
# until composer.json / composer.lock change.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-autoloader

COPY . .
RUN composer dump-autoload --no-dev --optimize \
    && mkdir -p cache public_html/files/uploaded \
    && chown www-data:www-data cache public_html/files/uploaded
