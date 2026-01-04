FROM php:8.2-cli

WORKDIR /app

COPY . /app

RUN mkdir -p /app/bootstrap/cache && chmod -R 777 /app/bootstrap/cache

RUN apt-get update \
    && apt-get install -y git unzip libpng-dev libonig-dev libxml2-dev libjpeg-dev libzip-dev zip curl \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install gd zip \
    # --- force rebuild ---
    && mkdir -p bootstrap/cache \
    && chmod -R 777 bootstrap/cache \
    # Install Node.js and npm
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader \
    && npm install \
    && npm run build \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache
    

# Railway rebuild trigger

RUN php -m && php -i

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]

# force rebuild

