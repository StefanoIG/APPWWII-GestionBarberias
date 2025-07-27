# Usar la imagen oficial de PHP 8.2 con FPM
FROM php:8.2-fpm

# Instalar dependencias del sistema necesarias para las extensiones de PHP
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    sqlite3 \
    libsqlite3-dev \
    libpq-dev \
    postgresql-client \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones de PHP
RUN docker-php-ext-install pdo pdo_sqlite pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip

# Obtener e instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer el directorio de trabajo
WORKDIR /var/www

# Copiar composer.json y composer.lock primero
COPY composer.json composer.lock ./

# Instalar dependencias de Composer
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copiar el resto del proyecto
COPY . .

# Copiar script de inicialización
COPY docker/scripts/init-laravel.sh /usr/local/bin/init-laravel.sh
RUN chmod +x /usr/local/bin/init-laravel.sh

# Cambiar permisos
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Exponer el puerto 9000 para PHP-FPM
EXPOSE 9000

# Ejecutar script de inicialización y luego PHP-FPM
CMD ["/usr/local/bin/init-laravel.sh"]
