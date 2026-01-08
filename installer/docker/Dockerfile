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
    icu-dev \
    linux-headers \
    $PHPIZE_DEPS

# Install PHP extensions required by Laravel/Larafactu
# Already compiled in PHP 8.4: ctype, fileinfo, tokenizer, pdo, xml (dom/simplexml)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# Install Redis extension (optional but recommended)
# Using printf to auto-answer PECL prompts (no to igbinary, no to lzf, etc.)
RUN printf '\n\n\n\n\n\n' | pecl install redis && docker-php-ext-enable redis

# Enable opcache
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.enable_cli=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

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