# syntax=docker/dockerfile:1
FROM php:8.1-cli-alpine3.16 AS base

RUN set -x \
    && addgroup -g 1000 app \
    && adduser -u 1000 -D -G app app

RUN set -xe \
    && apk add --update \
        icu \
    && apk add --no-cache --virtual .php-deps \
        make \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        zlib-dev \
        icu-dev \
        g++ \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        intl \
    && docker-php-ext-enable intl \
    && { find /usr/local/lib -type f -print0 | xargs -0r strip --strip-all -p 2>/dev/null || true; } \
    && apk del .build-deps \
    && rm -rf /tmp/* /usr/local/lib/php/doc/* /var/cache/apk/*

WORKDIR /opt/project

FROM composer:2 as composer

FROM base as source

ENV COMPOSER_HOME=/opt/.composer

RUN apk add --no-cache git

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

WORKDIR /opt/archived

# hadolint ignore=SC2215
RUN --mount=type=bind,source=./,rw,target=./ \
    mkdir -p /opt/project \
    && git archive --verbose --format tar HEAD | tar -x -C /opt/project

WORKDIR /opt/project

# hadolint ignore=SC2215
RUN --mount=type=bind,source=.composer/cache,target=/opt/.composer/cache \
    composer install --no-interaction --no-progress --no-dev --prefer-dist --classmap-authoritative

FROM base AS dev

COPY --chown=app:app --from=composer /usr/bin/composer /usr/local/bin/composer

RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug-stable \
	&& docker-php-ext-enable xdebug

USER app:app

VOLUME [ "/opt/project" ]
