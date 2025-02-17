FROM php:8.2-fpm

# Установка зависимостей
RUN apt update && apt install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev  # для PostgreSQL

RUN apt clean && rm -rf /var/lib/apt/lists/*

# Устанавливаем необходимые расширения PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd pdo_pgsql  # добавляем поддержку PostgreSQL

# Копируем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Указываем рабочую директорию
WORKDIR /var/www

# Используем стандартного пользователя (обычно это root или www-data)
USER www-data
