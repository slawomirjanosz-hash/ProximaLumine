
# Railway simple Dockerfile for Laravel
FROM php:8.2-cli

WORKDIR /app

COPY . /app

RUN apt-get update \
    && apt-get install -y git unzip libpng-dev libonig-dev libxml2-dev zip curl \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install gd \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader \
    && npm install \
    && npm run build \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 8080

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
