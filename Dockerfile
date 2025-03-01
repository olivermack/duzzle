FROM composer:latest

EXPOSE 9000
EXPOSE 9003

ENV XDEBUG_MODE=debug,develop

RUN apk add --no-cache $PHPIZE_DEPS \
    && apk add --update linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del $PHPIZE_DEPS linux-headers

WORKDIR /app
