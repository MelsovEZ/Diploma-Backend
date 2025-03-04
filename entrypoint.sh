#!/bin/sh

# Ждем, пока база данных будет доступна
echo "Ожидание базы данных..."
until nc -z -v -w30 $DB_HOST $DB_PORT; do
  echo "Ожидаем базу данных на $DB_HOST:$DB_PORT..."
  sleep 5
done
echo "База данных доступна!"

# Кешируем конфигурации и маршруты
php artisan config:cache
php artisan route:clear
php artisan route:cache

# Запускаем миграции
php artisan migrate --force

# Генерируем Swagger-документацию
php artisan l5-swagger:generate

# Запускаем Apache
exec apache2-foreground
