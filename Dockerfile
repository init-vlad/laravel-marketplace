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

COPY . .

# ---------- PRODUCTION ----------
# FROM base AS production
# RUN composer install --no-dev --optimize-autoloader --no-interaction --no-ansi --no-progress --quiet

# RUN mkdir -p storage/logs bootstrap/cache \
#  && chown -R www-data:www-data storage bootstrap/cache \
#  && chmod -R ug+rwx storage bootstrap/cache

# CMD ["sh", "php artisan octane:start --server=frankenphp --host=${OCTANE_HOST:-0.0.0.0} --port=${OCTANE_PORT:-8080}"]

# ---------- DEVELOPMENT ----------
FROM base AS development
RUN apt-get install -y --no-install-recommends
RUN composer install --quiet
ENTRYPOINT [""]
CMD ["sleep", "infinity"]

