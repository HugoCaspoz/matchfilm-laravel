FROM php:8.1-fpm

LABEL maintainer="Your Name"

# Argumentos para configuración
ARG user=www-data
ARG uid=1000

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    nodejs \
    npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalar Composer
COPY --from=composer:2.5.8 /usr/bin/composer /usr/bin/composer

# Crear directorio de la aplicación
WORKDIR /var/www

# Copiar solo composer.json y composer.lock primero para aprovechar la caché de Docker
COPY composer*.json ./

# Configurar memoria para Composer y ejecutar pre-instalación
RUN php -d memory_limit=-1 /usr/bin/composer install --no-scripts --no-autoloader --ignore-platform-reqs

# Copiar el código de la aplicación
COPY . .

# Generar autoloader optimizado y ejecutar scripts
RUN php -d memory_limit=-1 /usr/bin/composer dump-autoload --optimize --no-dev

# Instalar dependencias de Node.js y construir assets
RUN npm ci && npm run build

# Configurar permisos
RUN chmod -R 775 storage bootstrap/cache
RUN chown -R $user:$user /var/www

# Exponer puerto
EXPOSE 8000

# Iniciar servidor PHP
CMD php artisan serve --host=0.0.0.0 --port=8000