FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    mariadb-client
RUN docker-php-ext-install pdo_mysql zip

WORKDIR /var/www/html

CMD ["php-fpm"]
