# Используем PHP с Nginx и PHP-FPM
FROM php:8.2-fpm

# Устанавливаем системные зависимости для PHP
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Устанавливаем зависимости Laravel
WORKDIR /var/www/html
COPY . .

# Устанавливаем зависимости Laravel через Composer
RUN composer install --no-dev --optimize-autoloader

# Настроим права доступа для Laravel
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Копируем конфигурацию Nginx для работы с Laravel
COPY ./nginx/default.conf /etc/nginx/sites-available/default

# Открываем порты (80 для Laravel, 8080 для Swagger)
EXPOSE 80 8080

# Запускаем только PHP-FPM, так как nginx уже запустится в другом контейнере
CMD ["php-fpm"]
