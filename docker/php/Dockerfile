FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    git curl unzip bash \
    postgresql-dev rabbitmq-c-dev $PHPIZE_DEPS

RUN docker-php-ext-install pdo pdo_pgsql opcache && \
    pecl install amqp redis && \
    docker-php-ext-enable amqp redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html