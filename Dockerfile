# Используем PHP 8.2 с Apache
FROM php:8.2-apache

# Устанавливаем системные зависимости и драйверы
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev zip unzip git curl libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql pdo_pgsql

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Устанавливаем рабочую директорию
WORKDIR /var/www/html

# Копируем файлы Laravel
COPY . .

# Устанавливаем зависимости Laravel
RUN composer install --no-dev --optimize-autoloader \
    && composer require --dev zircote/swagger-php \
    && php artisan l5-swagger:generate \
    && ln -s /var/www/html/storage/api-docs /var/www/html/public/docs \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Настраиваем Apache для работы с Laravel
RUN sed -i 's|/var/www/html|/var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Добавляем ServerName для устранения предупреждений
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Открываем порт 80 для Apache
EXPOSE 80

# Копируем entrypoint.sh и даем права на выполнение
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Добавляем healthcheck
HEALTHCHECK --interval=30s --timeout=10s --retries=3 CMD curl -f http://localhost || exit 1

# Указываем entrypoint
ENTRYPOINT ["/entrypoint.sh"]
