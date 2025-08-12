#!/bin/bash

# Используем `/usr/bin/php` как наиболее вероятный путь.
PHP_BIN="/usr/bin/php"

set -e

echo "-> Включение режима обслуживания..."
$PHP_BIN artisan down --message="Обновление сайта. Скоро вернемся!" || true

echo "-> Загрузка последних изменений из Git..."
git pull origin main

echo "-> Установка зависимостей Composer..."
$PHP_BIN /usr/local/bin/composer install --no-dev --prefer-dist --optimize-autoloader

echo "-> Запуск миграций базы данных..."
$PHP_BIN artisan migrate --force

echo "-> Очистка кешей..."
$PHP_BIN artisan optimize:clear
$PHP_BIN artisan config:clear
$PHP_BIN artisan cache:clear
$PHP_BIN artisan route:clear
$PHP_BIN artisan view:clear

echo "-> Оптимизация приложения..."
$PHP_BIN artisan optimize
$PHP_BIN artisan config:cache
$PHP_BIN artisan event:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache

echo "Перезапуск воркеров Horizon..."
php artisan horizon:terminate

echo "-> Вывод сайта из режима обслуживания..."
$PHP_BIN artisan up

echo "Deployment completed successfully!"

echo "-> Обновление и перезапуск Supervisor..."
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start horizon
