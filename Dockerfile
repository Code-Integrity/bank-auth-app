# =========================================================================
# Production Environment: Backend Pipeline (PHP 8.4 + Web Server Orchestration)
# =========================================================================
FROM php:8.4-fpm-alpine

# Install essential system dependencies and required PHP extension arrays
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    libzip-dev \
    unzip \
    git \
    bash \
    postgresql-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql gd zip opcache

# Inject the latest Composer executable from the official upstream mirror
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy all application assets and runtime contexts
COPY . .

# Run production-grade Composer optimization sequences with zero dev overhead
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Enforce strict ownership boundaries and runtime permissions for core server caches
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Distribute server configuration parameters into the target container paths
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Swap runtime profiles to the official production php.ini with OPcache capabilities
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

EXPOSE 8080

# Provision the container entrypoint shell (Engineered to completely eradicate 419 Session Expiration Vectors)
RUN echo '#!/bin/sh' > /usr/local/bin/start.sh \
    && echo 'echo "⚙️ Creating required storage directories..."' >> /usr/local/bin/start.sh \
    && echo 'mkdir -p /var/www/html/database' >> /usr/local/bin/start.sh \
    && echo 'mkdir -p /var/www/html/storage/framework/sessions' >> /usr/local/bin/start.sh \
    && echo 'mkdir -p /var/www/html/storage/framework/views' >> /usr/local/bin/start.sh \
    && echo 'mkdir -p /var/www/html/storage/framework/cache' >> /usr/local/bin/start.sh \
    && echo 'mkdir -p /var/www/html/bootstrap/cache' >> /usr/local/bin/start.sh \
    && echo 'touch /var/www/html/database/database.sqlite' >> /usr/local/bin/start.sh \
    && echo 'echo "🔑 Enforcing strict ownership and permissions..."' >> /usr/local/bin/start.sh \
    && echo 'chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database' >> /usr/local/bin/start.sh \
    && echo 'chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database' >> /usr/local/bin/start.sh \
    && echo 'php artisan migrate:fresh --seed --force' >> /usr/local/bin/start.sh \
    && echo 'php artisan config:clear' >> /usr/local/bin/start.sh \
    && echo 'php artisan route:clear' >> /usr/local/bin/start.sh \
    && echo 'php artisan view:clear' >> /usr/local/bin/start.sh \
    && echo 'php artisan cache:clear' >> /usr/local/bin/start.sh \
    && echo '/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf' >> /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]
