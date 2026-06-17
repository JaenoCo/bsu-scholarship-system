FROM php:8.2-cli

# System dependencies
RUN apt-get update && apt-get install -y \
    git curl unzip zip libpq-dev nodejs npm

# PHP extensions (PostgreSQL)
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

# Install backend dependencies
RUN composer install --no-dev --optimize-autoloader

# Install frontend dependencies
RUN npm install && npm run build

# Permissions
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 10000

CMD ["sh", "start.sh"]