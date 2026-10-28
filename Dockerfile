FROM composer:2 AS vendor
WORKDIR /app
COPY web/html/composer.json web/html/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist
COPY web/html/src ./src
RUN composer dump-autoload --no-dev --optimize

FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libxml2-dev libcurl4-openssl-dev libsqlite3-dev \
    && docker-php-ext-install pdo_mysql pdo_sqlite dom xml \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY docker/dev-sso/ /usr/local/share/dev-sso/
COPY docker/seed/seed.sh /usr/local/bin/seed.sh
COPY docker/seed/split-metadata.php /usr/local/share/seed/split-metadata.php

# Layout mirrors the repository's web/ dir: the app resolves ../../schemas
# relative to html/admin and scripts require ../html/vendor/autoload.php
COPY web/ /var/www/
COPY --from=vendor /app/vendor /var/www/html/vendor
# config.php is mounted by docker-compose from docker/dev-config/

RUN chmod +x /usr/local/bin/entrypoint.sh /usr/local/bin/seed.sh \
    && mkdir -p /opt/metadata \
    && chown -R www-data:www-data /opt/metadata

EXPOSE 80
ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
