# Используем PHP с Nginx и PHP-FPM
FROM php:8.2-fpm

# Устанавливаем системные зависимости
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    nginx \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Устанавливаем зависимости Laravel
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --optimize-autoloader

# Настраиваем права доступа
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Настройка Nginx
COPY ./nginx/default.conf /etc/nginx/sites-available/default

# Открываем порты (80 для Laravel, 8080 для Swagger)
EXPOSE 80 8080

# Запускаем Nginx и PHP-FPM
CMD service nginx start && php-fpm
