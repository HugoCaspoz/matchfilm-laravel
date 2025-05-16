# Etapa de construcción
FROM php:8.3-cli as builder

# Instalar dependencias y extensiones
RUN apt-get update && apt-get install -y \
    git zip unzip libpng-dev libonig-dev libxml2-dev \
    curl libcurl4-openssl-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring zip curl gd xml

# Aumentar límite de memoria
RUN echo "memory_limit=-1" > /usr/local/etc/php/conf.d/memory-limit.ini

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /app

# Copiar todos los archivos
COPY . .

# Instalar dependencias
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Etapa final
FROM php:8.3-cli

# Instalar solo las extensiones necesarias para producción
RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring zip gd xml

# Configurar directorio de trabajo
WORKDIR /app

# Copiar la aplicación desde la etapa de construcción
COPY --from=builder /app /app

# Configurar permisos
RUN chmod -R 755 /app/storage /app/bootstrap/cache

# Exponer puerto
EXPOSE 8000

# Comando para iniciar la aplicación
CMD php artisan serve --host=0.0.0.0 --port=8000