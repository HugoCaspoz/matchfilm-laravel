FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev npm \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath zip xml opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN php -v && php -m

RUN composer install --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs -vvv

COPY . .

RUN npm ci && npm run build

RUN chmod +x artisan

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
