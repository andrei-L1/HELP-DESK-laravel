FROM richarvey/nginx-php-fpm:latest   # Or :8.2 if you want pinned; latest is PHP 8.2.7

WORKDIR /var/www/html

# Copy app code
COPY . .

# Env vars for the base image
ENV SKIP_COMPOSER=1
ENV WEBROOT=/var/www/html/public
ENV PHP_ERRORS_STDERR=1
ENV RUN_SCRIPTS=1
ENV REAL_IP_HEADER=1

# Laravel production env (can be overridden in Render dashboard)
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr

ENV COMPOSER_ALLOW_SUPERUSER=1

# Install composer deps
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Build frontend assets
RUN npm ci && npm run build

# Clear caches and link storage (safe at build if no dynamic env)
RUN php artisan optimize:clear \
    && php artisan config:cache --no-interaction \
    && php artisan route:cache --no-interaction \
    && php artisan view:cache --no-interaction \
    && php artisan storage:link --no-interaction || true

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Base image starts nginx + php-fpm automatically — no CMD needed