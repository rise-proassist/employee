FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       git \
       unzip \
       libzip-dev \
       libpng-dev \
       libjpeg-dev \
       libfreetype6-dev \
       libonig-dev \
       libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mysqli mbstring zip gd \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

RUN printf "<VirtualHost *:80>\n    DocumentRoot /var/www/html/htdocs\n    <Directory /var/www/html/htdocs>\n        AllowOverride All\n        Require all granted\n    </Directory>\n</VirtualHost>\n" > /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
