#!/bin/bash

# Скрипт первоначальной настройки сервера для Laravel проекта
# Запускать на сервере от root

set -e

PROJECT_NAME="truck-impo-main"
PROJECT_PATH="/var/www/$PROJECT_NAME"
GIT_REPO="https://github.com/zdanovichd/truck-impo.git"

echo "=========================================="
echo "Настройка сервера для Laravel проекта"
echo "=========================================="

# Обновление системы
echo "-> Обновление пакетов..."
apt-get update -y
apt-get upgrade -y

# Установка необходимых пакетов
echo "-> Установка необходимых пакетов..."
apt-get install -y \
    software-properties-common \
    curl \
    git \
    unzip \
    nginx \
    mysql-server \
    redis-server

# Установка PHP и расширений
echo "-> Установка PHP 8.2 и расширений..."
add-apt-repository ppa:ondrej/php -y
apt-get update -y
apt-get install -y \
    php8.2-fpm \
    php8.2-cli \
    php8.2-common \
    php8.2-mysql \
    php8.2-xml \
    php8.2-curl \
    php8.2-mbstring \
    php8.2-zip \
    php8.2-bcmath \
    php8.2-redis \
    php8.2-ctype \
    php8.2-dom \
    php8.2-fileinfo \
    php8.2-openssl \
    php8.2-pdo \
    php8.2-tokenizer

# Установка Composer
echo "-> Установка Composer..."
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
    echo "Composer установлен"
else
    echo "Composer уже установлен"
fi

# Установка Supervisor
echo "-> Установка Supervisor..."
apt-get install -y supervisor

# Создание директории проекта
echo "-> Создание директории проекта..."
mkdir -p /var/www
cd /var/www

# Клонирование проекта
if [ -d "$PROJECT_PATH" ]; then
    echo "-> Проект уже существует, обновляем..."
    cd $PROJECT_PATH
    git pull origin main || true
else
    echo "-> Клонирование проекта из Git..."
    git clone $GIT_REPO $PROJECT_NAME
    cd $PROJECT_PATH
fi

# Установка зависимостей
echo "-> Установка зависимостей Composer..."
composer install --no-dev --optimize-autoloader

# Настройка .env
if [ ! -f .env ]; then
    echo "-> Создание файла .env..."
    if [ -f .env.example ]; then
        cp .env.example .env
        php artisan key:generate
        echo "⚠️  ВАЖНО: Отредактируйте файл .env и настройте базу данных!"
        echo "   nano $PROJECT_PATH/.env"
    else
        echo "⚠️  ВНИМАНИЕ: Файл .env.example не найден!"
    fi
else
    echo "-> Файл .env уже существует"
fi

# Настройка прав доступа
echo "-> Настройка прав доступа..."
chown -R www-data:www-data $PROJECT_PATH
chmod -R 755 $PROJECT_PATH
chmod -R 775 $PROJECT_PATH/storage
chmod -R 775 $PROJECT_PATH/bootstrap/cache

# Настройка Nginx
echo "-> Настройка Nginx..."
NGINX_CONFIG="/etc/nginx/sites-available/$PROJECT_NAME"

cat > $NGINX_CONFIG <<EOF
server {
    listen 80;
    server_name 155.212.221.20;
    root $PROJECT_PATH/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Активация конфигурации Nginx
ln -sf $NGINX_CONFIG /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Проверка конфигурации Nginx
nginx -t

# Перезапуск сервисов
echo "-> Перезапуск сервисов..."
systemctl restart nginx
systemctl restart php8.2-fpm
systemctl restart redis-server
systemctl enable nginx
systemctl enable php8.2-fpm
systemctl enable redis-server

echo ""
echo "=========================================="
echo "Настройка завершена!"
echo "=========================================="
echo ""
echo "Следующие шаги:"
echo "1. Настройте базу данных MySQL:"
echo "   mysql -u root -p"
echo "   CREATE DATABASE truck_impo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo ""
echo "2. Отредактируйте файл .env:"
echo "   nano $PROJECT_PATH/.env"
echo "   Установите DB_DATABASE, DB_USERNAME, DB_PASSWORD"
echo ""
echo "3. Запустите миграции:"
echo "   cd $PROJECT_PATH"
echo "   php artisan migrate --force"
echo ""
echo "4. Оптимизируйте приложение:"
echo "   php artisan optimize"
echo "   php artisan config:cache"
echo "   php artisan route:cache"
echo "   php artisan view:cache"
echo ""
echo "5. Откройте в браузере: http://155.212.221.20"
echo ""

