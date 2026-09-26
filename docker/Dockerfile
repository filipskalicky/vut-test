FROM php:8.2-apache

# intl is required by CodeIgniter 4; mysqli talks to MariaDB the same way Vedos does.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libzip-dev \
    && docker-php-ext-install -j"$(nproc)" intl mysqli pdo_mysql \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY apache.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
