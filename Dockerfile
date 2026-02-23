# Stage 1: Install Composer dependencies
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --ignore-platform-reqs --no-scripts --no-autoloader

# Stage 2: Build frontend assets
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
# Vite needs Ziggy's Vue components from the vendor directory
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

# Stage 2: Serve application
FROM richarvey/nginx-php-fpm:latest

WORKDIR /var/www/html

# Copy project files
COPY . .

# Override default Nginx default.conf to support Laravel routing
COPY nginx.conf /etc/nginx/sites-available/default.conf

# Copy compiled frontend assets from the node stage
COPY --from=frontend /app/public/build ./public/build
# Image configuration from base image docs
ENV SKIP_COMPOSER=1
ENV WEBROOT=/var/www/html/public
ENV PHP_ERRORS_STDERR=1
ENV RUN_SCRIPTS=1
ENV REAL_IP_HEADER=1

# Laravel production settings
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr

# Allow composer as root during build
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Prepare database directory
RUN mkdir -p database \
    && touch database/database.sqlite \
    && chown -R nginx:nginx database storage bootstrap/cache

# Create startup script to run Laravel migrations and cache on container boot
RUN mkdir -p scripts \
    && echo '#!/bin/bash' > scripts/00-laravel-setup.sh \
    && echo 'echo "Running Laravel Optimizations and Migrations..."' >> scripts/00-laravel-setup.sh \
    && echo 'php artisan optimize:clear' >> scripts/00-laravel-setup.sh \
    && echo 'php artisan optimize' >> scripts/00-laravel-setup.sh \
    && echo 'php artisan migrate --force --isolated' >> scripts/00-laravel-setup.sh \
    && echo 'php artisan storage:link' >> scripts/00-laravel-setup.sh \
    && echo 'chown -R nginx:nginx database storage bootstrap/cache' >> scripts/00-laravel-setup.sh \
    && chmod +x scripts/00-laravel-setup.sh
