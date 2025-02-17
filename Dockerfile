# Используем PHP с Apache
FROM php:8.2-apache

# Устанавливаем системные зависимости
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
RUN composer install --no-dev --optimize-autoloader

# Настроим Apache для работы с Laravel
RUN sed -i 's|/var/www/html|/var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Добавляем ServerName для устранения предупреждения
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Настраиваем права доступа
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Открываем порты (80 для Laravel)
EXPOSE 80

# Запускаем Apache
CMD ["apache2-foreground"]
