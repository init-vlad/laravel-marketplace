# docker/common/Dockerfile
FROM dunglas/frankenphp:php8.3 AS base

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip \
    libpq-dev libzip-dev libicu-dev \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install pdo_pgsql intl bcmath pcntl zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN [ -f /usr/local/bin/frankenphp ] || ln -s /usr/bin/frankenphp /usr/local/bin/frankenphp
WORKDIR /var/www

# ---------- DEVELOPMENT ----------
FROM base AS development
CMD ["sh", "-c", "php artisan octane:start --server=frankenphp --host=${HOST:-0.0.0.0} --port=${LARAVEL_PORT:-8080} --watch"]

# ---------- PRODUCTION ----------
FROM base AS production
COPY . .
RUN composer install --no-dev --optimize-autoloader
RUN php artisan migrate
RUN php artisan db:seed
RUN php artisan optimize
CMD ["sh", "-c", "frankenphp run --workers=${LARAVEL_OCTANE_WORKERS:-8} --max-requests=${LARAVEL_OCTANE_MAX_REQUESTS:-1000} --static=/var/www/public --host=${HOST:-0.0.0.0} --port=${LARAVEL_PORT:-8080}"]
