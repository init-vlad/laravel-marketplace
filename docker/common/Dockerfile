# docker/common/Dockerfile
FROM php:8.3-cli AS common
RUN apt-get update && apt-get install -y \
    unzip git \
    libzip-dev libonig-dev libxml2-dev libicu-dev libpq-dev \
    && pecl install redis && docker-php-ext-enable redis \
    && docker-php-ext-install zip bcmath pdo pdo_pgsql intl pcntl
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /var/www

# development stage
FROM common AS development
COPY . .
RUN composer install
EXPOSE 4000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=4000"]

# production stage
FROM common AS production
COPY . .
RUN composer install --no-dev --optimize-autoloader
EXPOSE 4000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=4000"]
