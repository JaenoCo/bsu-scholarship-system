FROM php:8.2-fpm

# Install system dependencies, Nginx, and Supervisor
RUN apt-get update && apt-get install -y \
    git curl unzip zip \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    nginx \
    supervisor \
    nodejs \
    npm

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Copy Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install dependencies and build frontend assets
RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN npm install && npm run build

# Copy configuration files (Make sure these match your project)
COPY .docker/nginx.conf /etc/nginx/sites-available/default
COPY .docker/supervisor.conf /etc/supervisor/conf.d/supervisor.conf

# Set production permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Render automatically maps its traffic here; EXPOSE is optional but good practice
EXPOSE 8080

# This fixes your migration and startup logic cleanly
CMD php artisan config:clear \
 && php artisan cache:clear \
 && php artisan migrate --force \
 && /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisor.conf
