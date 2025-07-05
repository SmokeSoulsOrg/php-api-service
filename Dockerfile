FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev \
    libzip-dev libpq-dev libjpeg-dev libfreetype6-dev \
    default-mysql-client \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd

# Avoid Git "dubious ownership" errors inside container
RUN git config --global --add safe.directory /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Set permissions and make scripts executable
RUN chown -R www-data:www-data /var/www/html \
    && chmod +x docker/laravel/init-migrate.sh

# Install PHP deps
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

USER root

# Entrypoint: run init-migrate.sh
ENTRYPOINT ["./docker/laravel/init-migrate.sh"]

CMD ["php-fpm"]
