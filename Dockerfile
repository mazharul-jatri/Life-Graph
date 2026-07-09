FROM php:8.3-fpm
RUN apt-get update && apt-get install -y libzip-dev zip unzip git curl \
    && docker-php-ext-install pdo pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-interaction
run chown -R www-data:www-data storage bootstrap/cache

expose 9000
