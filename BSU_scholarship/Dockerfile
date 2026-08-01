FROM php:8.2-fpm AS base

RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl unzip zip ca-certificates \
    libpq-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev libzip-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

FROM base AS builder

RUN apt-get update && apt-get install -y --no-install-recommends nodejs npm \
    && rm -rf /var/lib/apt/lists/*

COPY composer.json composer.lock ./
COPY artisan ./
COPY bootstrap/app.php bootstrap/app.php
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

COPY package*.json ./
RUN npm install --silent

COPY . .
RUN npm run build
RUN composer dump-autoload --optimize --no-interaction

FROM base AS runtime

RUN apt-get update && apt-get install -y --no-install-recommends nginx supervisor \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=builder /var/www/html /var/www/html
COPY .docker/nginx.conf /etc/nginx/sites-available/default
COPY .docker/supervisor.conf /etc/supervisor/conf.d/supervisor.conf
COPY start.sh /var/www/html/start.sh

RUN chmod +x /var/www/html/start.sh \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8080
ENTRYPOINT ["/var/www/html/start.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisor.conf"]