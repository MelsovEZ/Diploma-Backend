# Используем PHP 8.2 с Apache
FROM php:8.2-apache

RUN echo "FORCE REBUILD"

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
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql pdo_pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Устанавливаем рабочую директорию
WORKDIR /var/www/html

# Копируем файлы composer перед основными файлами для кэширования зависимостей
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# Копируем все файлы проекта
COPY . .

# Устанавливаем swagger-php для генерации документации
RUN composer require --dev zircote/swagger-php && rm -rf /root/.composer/cache

# Генерация Swagger документации
RUN php artisan l5-swagger:generate

# Создаем символическую ссылку на Swagger JSON в public/docs
RUN ln -s /var/www/html/storage/api-docs /var/www/html/public/docs

# Настраиваем Apache для работы с Laravel
RUN sed -i 's|/var/www/html|/var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Добавляем ServerName для устранения предупреждений
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Настраиваем права доступа
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Открываем порт 80 для Apache
EXPOSE 80

# Запускаем Laravel команды перед стартом Apache, ожидая базу данных
CMD php artisan config:cache && \
    php artisan route:clear && \
    php artisan route:cache && \
    until php artisan migrate --force; do echo "Ожидание БД..."; sleep 3; done && \
    php artisan l5-swagger:generate && \
    apache2-foreground
