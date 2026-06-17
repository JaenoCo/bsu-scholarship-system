FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libpq-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev

# Install PHP extensions (IMPORTANT for Laravel stability)
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Install frontend dependencies + build assets
RUN npm install
RUN npm run build

# Laravel permissions (VERY IMPORTANT)
RUN chmod -R 775 storage bootstrap/cache

# Clear cached config (prevents env mismatch issues)
RUN php artisan migrate --force
RUN php artisan config:clear
RUN php artisan cache:clear
RUN php artisan view:clear


# Expose port
EXPOSE 10000

# Start Laravel
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-10000}