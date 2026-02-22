FROM richarvey/nginx-php-fpm:latest

WORKDIR /var/www/html

# Copy project files
COPY . .

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

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get update \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Install and build frontend assets
RUN npm ci && npm run build

# Optimize Laravel
RUN php artisan optimize:clear \
    && php artisan config:cache --no-interaction \
    && php artisan route:cache --no-interaction \
    && php artisan view:cache --no-interaction \
    && php artisan storage:link --no-interaction || true

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Copy custom nginx config
COPY nginx-site.conf /etc/nginx/conf.d/default.conf