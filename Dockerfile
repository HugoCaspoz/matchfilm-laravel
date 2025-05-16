FROM php:8.3-cli

# Instalar dependencias mínimas
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    curl \
    libcurl4-openssl-dev \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring zip curl

# Instalar extensiones PHP esenciales
RUN docker-php-ext-install pdo_mysql mbstring

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /app

# Copiar archivos de Composer
COPY composer.json composer.lock ./

# 🔧 Instalar dependencias
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Copiar el resto del proyecto
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY public ./public
COPY resources ./resources
COPY routes ./routes
COPY storage ./storage
COPY artisan ./

# Configurar permisos
RUN chmod -R 755 /app/storage /app/bootstrap/cache

# Exponer puerto
EXPOSE 8000

# Comando para iniciar la aplicación
CMD php artisan serve --host=0.0.0.0 --port=8000