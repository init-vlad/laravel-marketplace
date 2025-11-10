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
COPY . .
RUN composer install
EXPOSE 8000
# CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=8000", "--watch"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

# ---------- PRODUCTION ----------
FROM base AS production
COPY . .
RUN composer install --no-dev --optimize-autoloader
RUN php artisan optimize
EXPOSE 8000
CMD ["frankenphp", "run", "--workers=8", "--max-requests=1000", "--static=/var/www/public", "--host=0.0.0.0", "--port=8000"]
