# Используем PHP 8.2 с Apache
FROM php:8.2-apache

# Устанавливаем системные зависимости и PostgreSQL драйвер
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

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Устанавливаем рабочую директорию
WORKDIR /var/www/html

# Копируем файлы Laravel
COPY . .

# Устанавливаем пакет для работы с S3 и MinIO
RUN composer require league/flysystem-aws-s3-v3

# Устанавливаем зависимости Laravel
RUN composer install --no-dev --optimize-autoloader

# Устанавливаем swagger-php для генерации документации
RUN composer require --dev zircote/swagger-php

# Убеждаемся, что папки `storage/api-docs` и `public/swagger-ui` существуют
RUN mkdir -p /var/www/html/storage/api-docs && \
    mkdir -p /var/www/html/public/api-docs && \
    mkdir -p /var/www/html/public/swagger-ui

# Настраиваем права доступа
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Публикуем файлы Swagger UI
RUN php artisan vendor:publish --provider="L5Swagger\\L5SwaggerServiceProvider" --tag=config
RUN php artisan vendor:publish --provider="L5Swagger\\L5SwaggerServiceProvider" --tag=public --force

# Генерация Swagger документации
RUN php artisan l5-swagger:generate

# Проверяем наличие api-docs.json
RUN ls -la /var/www/html/storage/api-docs

# Переносим Swagger JSON в `public/api-docs`
RUN cp /var/www/html/storage/api-docs/api-docs.json /var/www/html/public/api-docs/api-docs.json || true

# Устанавливаем правильные права на Swagger JSON
RUN chmod -R 775 /var/www/html/storage/api-docs /var/www/html/public/swagger-ui /var/www/html/public/api-docs && \
    chown -R www-data:www-data /var/www/html/storage/api-docs /var/www/html/public/swagger-ui /var/www/html/public/api-docs

# Настраиваем Apache для работы с Laravel
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf \
    && a2enmod rewrite

# Добавляем ServerName для устранения предупреждений
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Открываем порт 80 для Apache
EXPOSE 80

# Запускаем Laravel команды перед стартом Apache
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
