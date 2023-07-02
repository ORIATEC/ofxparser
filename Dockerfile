FROM php:7.4-fpm-alpine
RUN apk update && apk add build-base
RUN curl -sS https://getcomposer.org/installer | php \
        && mv composer.phar /usr/local/bin/ \
        && ln -s /usr/local/bin/composer.phar /usr/local/bin/composer
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug-2.9.2 \
    && docker-php-ext-enable xdebug
WORKDIR /app
ENV PATH="~/.composer/vendor/bin:./vendor/bin:${PATH}"
