# Usa PHP 8.2 con FPM
FROM php:8.2-fpm

# Instala dependencias del sistema y extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    npm \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath zip xml opcache

# Instala Composer (desde la imagen oficial)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define el directorio de trabajo
WORKDIR /app

# Copia los archivos composer primero para aprovechar cache de docker
COPY composer.json composer.lock ./

# Instala dependencias PHP
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs

# Copia el resto del proyecto
COPY . .

# Instala dependencias de Node y construye assets (si usas npm/yarn/vite)
RUN npm ci && npm run build

# Da permisos de ejecución al archivo artisan
RUN chmod +x artisan

# Expone el puerto (puedes cambiarlo si Railway requiere otro)
EXPOSE 8000

# Comando para arrancar Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
