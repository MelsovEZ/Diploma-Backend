FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    libpq-dev \
    ca-certificates \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql pdo_pgsql \
    && update-ca-certificates

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer require league/flysystem-aws-s3-v3

RUN composer require intervention/image

RUN composer install --no-dev --optimize-autoloader

RUN composer require --dev zircote/swagger-php

RUN mkdir -p /var/www/html/storage/api-docs && \
    mkdir -p /var/www/html/public/api-docs && \
    mkdir -p /var/www/html/public/swagger-ui

RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

RUN php artisan vendor:publish --provider="L5Swagger\\L5SwaggerServiceProvider" --tag=config
RUN php artisan vendor:publish --provider="L5Swagger\\L5SwaggerServiceProvider" --tag=public --force

RUN php artisan l5-swagger:generate

RUN ls -la /var/www/html/storage/api-docs

RUN cp /var/www/html/storage/api-docs/api-docs.json /var/www/html/public/api-docs/api-docs.json || true

RUN chmod -R 775 /var/www/html/storage/api-docs /var/www/html/public/swagger-ui /var/www/html/public/api-docs && \
    chown -R www-data:www-data /var/www/html/storage/api-docs /var/www/html/public/swagger-ui /var/www/html/public/api-docs

RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf \
    && a2enmod rewrite

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80

CMD php artisan config:clear && \
    php artisan cache:clear && \
    php artisan view:clear && \
    php artisan route:clear && \
    php artisan route:cache && \
    php artisan migrate --force && \
    php artisan vendor:publish --provider="L5Swagger\\L5SwaggerServiceProvider" --tag=public --force && \
    php artisan l5-swagger:generate && \
    cp -r vendor/swagger-api/swagger-ui/dist/* /var/www/html/public/swagger-ui/ && \
    chmod -R 775 /var/www/html/storage/api-docs /var/www/html/public/swagger-ui /var/www/html/public/api-docs && \
    chown -R www-data:www-data /var/www/html/storage/api-docs /var/www/html/public/swagger-ui /var/www/html/public/api-docs && \
    cp /var/www/html/storage/api-docs/api-docs.json /var/www/html/public/api-docs/api-docs.json || true && \
    apache2-foreground
