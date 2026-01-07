# Larafactu Installer - PHP 8.4 Testing Image

FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    bash \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    mysql-client \
    oniguruma-dev \
    libxml2-dev \
    icu-dev

# Install PHP extensions required by Laravel/Larafactu
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        xml \
        intl \
        opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/installer

# Configure PHP for development
RUN echo "display_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-errors.ini \
    && echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/docker-php-ext-errors.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/docker-php-ext-memory.ini

# Create non-root user
RUN addgroup -g 1000 larafactu \
    && adduser -u 1000 -G larafactu -s /bin/bash -D larafactu

# Set permissions
RUN chown -R larafactu:larafactu /var/www

USER larafactu

EXPOSE 9000

CMD ["php-fpm"]

