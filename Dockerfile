FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip libonig-dev libxml2-dev libzip-dev libpng-dev cron procps supervisor\
    openssl iputils-ping dnsutils telnet libnss3-tools tzdata\
    && docker-php-ext-install pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV TZ=Asia/Ho_Chi_Minh
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug
    
WORKDIR /var/www
    
COPY src/ /var/www
COPY ./docker/php/conf.d/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY ./docker/php/conf.d/custom.ini /usr/local/etc/php/conf.d/custom.ini

RUN composer install

RUN chmod -R 777 /var/www 

COPY ./docker/laravel/laravel-cron /etc/laravel-cron

RUN chmod 0644 /etc/laravel-cron

EXPOSE 9000

# USER www-data

CMD ["php-fpm"]

