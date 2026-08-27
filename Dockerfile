# ==========================================
# バックエンド（PHP 8.4 + Webサーバー）の本番環境
# ==========================================
FROM php:8.4-fpm-alpine

# 必要なシステムパッケージとPHP拡張のインストール
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
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql gd zip opcache

# Composerの導入
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 全てのアプリケーションファイルをコピー
COPY . .

# 本番環境用の最適化をかけたComposerインストール
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 権限の厳格な設定（Webサーバーが書き込めるように）
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 設定ファイルの配置
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 本番用PHPの最適化設定 (OPcache等)
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

EXPOSE 8080

# 起動用エントリーポイントスクリプトの作成（本番環境用パーミッション完全固定版）
RUN echo '#!/bin/sh' > /usr/local/bin/start.sh \
    && echo 'mkdir -p /var/www/html/database' >> /usr/local/bin/start.sh \
    && echo 'touch /var/www/html/database/database.sqlite' >> /usr/local/bin/start.sh \
    && echo 'chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database' >> /usr/local/bin/start.sh \
    && echo 'chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database' >> /usr/local/bin/start.sh \
    && echo 'php artisan migrate:fresh --seed --force' >> /usr/local/bin/start.sh \
    && echo 'php artisan config:cache' >> /usr/local/bin/start.sh \
    && echo 'php artisan route:cache' >> /usr/local/bin/start.sh \
    && echo 'php artisan view:cache' >> /usr/local/bin/start.sh \
    && echo '/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf' >> /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]

