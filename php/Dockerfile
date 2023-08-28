FROM php:8.1-fpm-alpine as app


COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN set -eux; \
    install-php-extensions pdo pdo_pgsql;

#RUN docker-php-ext-install pdo RUN composer install --prefer-dist --no-dev --no-scripts --no-progress --no-interactiondo_mysql

ENV COMPOSER_ALLOW_SUPERUSER=1

# obtain composer using multi-stage build
# https://docs.docker.com/build/building/multi-stage/
COPY --from=composer:2.4 /usr/bin/composer /usr/bin/composer

# /var/www/html is the default working directory for this image
#WORKDIR '/app'

COPY ./app/composer.* ./

RUN composer install --prefer-dist --no-dev --no-scripts --no-progress --no-interaction

COPY ./app .

RUN composer dump-autoload --optimize

FROM app AS app_dev

ENV XDEBUG_MODE=off

COPY ./php/conf.d/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

RUN set -eux; \
	install-php-extensions xdebug