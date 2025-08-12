#!/bin/bash

# --- Настройка strict mode для безопасного выполнения ---
set -e

# --- Переменные ---
# Замените 'your-project-name.com' на домен вашего проекта
DOMAIN="your-project-name.com"
# Замените 'www-data' на пользователя, под которым будет работать Nginx
WEB_USER="www-data"

echo "-> Обновление пакетов сервера..."
sudo apt-get update -y
sudo apt-get upgrade -y

# --- Установка Nginx ---
echo "-> Установка Nginx..."
sudo apt-get install nginx -y

# --- Установка PHP и необходимых расширений ---
echo "-> Установка PHP 8.2 и расширений..."
sudo apt-get install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt-get update -y
sudo apt-get install php8.2-fpm php8.2-common php8.2-mysql php8.2-pgsql php8.2-xml php8.2-curl php8.2-mbstring php8.2-zip php8.2-bcmath php8.2-redis -y
# Дополнительные расширения
sudo apt-get install php8.2-ctype php8.2-dom php8.2-fileinfo php8.2-filter php8.2-hash php8.2-openssl php8.2-pcre php8.2-pdo php8.2-session php8.2-tokenizer -y

# --- Установка Composer ---
echo "-> Установка Composer..."
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# --- Установка Redis ---
echo "-> Установка Redis..."
sudo apt-get install redis-server -y
sudo phpenmod redis

# --- Установка Supervisor ---
echo "-> Установка Supervisor..."
sudo apt-get install supervisor -y

# --- Настройка Nginx для Laravel ---
echo "-> Настройка Nginx для Laravel..."

# Удаляем дефолтный конфиг
sudo rm /etc/nginx/sites-enabled/default

# Создаем новый файл конфигурации Nginx для вашего домена
NGINX_CONFIG=$(cat <<EOF
server {
    listen 80;
    server_name $DOMAIN;
    root /var/www/$DOMAIN/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html;

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
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
)

echo "$NGINX_CONFIG" | sudo tee /etc/nginx/sites-available/$DOMAIN
sudo ln -s /etc/nginx/sites-available/$DOMAIN /etc/nginx/sites-enabled/

# --- Настройка прав доступа к директории проекта ---
echo "-> Создание директории проекта и настройка прав доступа..."
sudo mkdir -p /var/www/$DOMAIN
sudo chown -R $USER:$WEB_USER /var/www/$DOMAIN
sudo chmod -R 775 /var/www/$DOMAIN
sudo chmod -R o-w /var/www/$DOMAIN

# --- Завершение ---
echo "-> Перезапуск Nginx и PHP-FPM..."
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo systemctl enable nginx
sudo systemctl enable php8.2-fpm
sudo systemctl enable redis-server

echo "Настройка сервера завершена. Теперь вы можете развернуть свой проект в /var/www/$DOMAIN"
