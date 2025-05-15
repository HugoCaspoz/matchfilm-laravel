FROM php:8.1-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Instalar Composer
COPY --from=composer:2.5.8 /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Copiar todo el código de la aplicación
COPY . .

# Instalar dependencias de Composer en un solo paso
# Esto evita problemas con dump-autoload
RUN php -d memory_limit=-1 /usr/bin/composer install --no-interaction --no-dev --optimize-autoloader

# Instalar dependencias de Node.js y construir assets
RUN npm ci && npm run build

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Exponer puerto
EXPOSE 8000

# Iniciar servidor PHP
CMD php artisan serve --host=0.0.0.0 --port=8000