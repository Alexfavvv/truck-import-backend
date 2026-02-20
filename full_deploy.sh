#!/bin/bash

# Полный скрипт деплоя Laravel проекта на сервер
# Запускать на сервере от root: bash full_deploy.sh

set -e

PROJECT_NAME="truck-impo-main"
PROJECT_PATH="/var/www/$PROJECT_NAME"
GIT_REPO="https://github.com/zdanovichd/truck-impo.git"

echo "=========================================="
echo "🚀 Начинаем деплой проекта truck-impo"
echo "=========================================="
echo ""

# Шаг 1: Проверка окружения
echo "📋 Шаг 1: Проверка окружения..."
echo "-> Проверка PHP..."
if command -v php &> /dev/null; then
    php -v | head -n 1
else
    echo "❌ PHP не установлен!"
    exit 1
fi

echo "-> Проверка Composer..."
if command -v composer &> /dev/null; then
    composer --version
else
    echo "❌ Composer не установлен!"
    exit 1
fi

echo "-> Проверка Git..."
if command -v git &> /dev/null; then
    git --version
else
    echo "❌ Git не установлен!"
    exit 1
fi

echo "-> Проверка Nginx..."
if command -v nginx &> /dev/null; then
    nginx -v 2>&1
else
    echo "❌ Nginx не установлен!"
    exit 1
fi

echo ""
echo "✅ Все необходимые компоненты установлены"
echo ""

# Шаг 2: Клонирование/обновление проекта
echo "📦 Шаг 2: Клонирование/обновление проекта..."
mkdir -p /var/www
cd /var/www

if [ -d "$PROJECT_PATH" ]; then
    echo "-> Проект существует, обновляем..."
    cd $PROJECT_PATH
    
    # Сохраняем .env если есть
    if [ -f .env ]; then
        cp .env .env.backup
        echo "-> Создана резервная копия .env"
    fi
    
    git pull origin main
    echo "✅ Проект обновлен"
else
    echo "-> Клонируем проект..."
    git clone $GIT_REPO $PROJECT_NAME
    cd $PROJECT_PATH
    echo "✅ Проект клонирован"
fi

echo ""

# Шаг 3: Установка зависимостей
echo "📚 Шаг 3: Установка зависимостей Composer..."
composer install --no-dev --optimize-autoloader --no-interaction
echo "✅ Зависимости установлены"
echo ""

# Шаг 4: Настройка .env
echo "⚙️  Шаг 4: Настройка .env файла..."
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        php artisan key:generate --force
        echo "✅ Создан файл .env и сгенерирован ключ"
        echo "⚠️  ВАЖНО: Отредактируйте файл .env и настройте:"
        echo "   - APP_ENV=production"
        echo "   - APP_DEBUG=false"
        echo "   - DB_DATABASE, DB_USERNAME, DB_PASSWORD"
        echo ""
        echo "Выполните: nano $PROJECT_PATH/.env"
        echo ""
        read -p "Нажмите Enter после настройки .env файла..."
    else
        echo "❌ Файл .env.example не найден!"
        exit 1
    fi
elif [ -f .env.backup ]; then
    echo "-> Восстановлен существующий .env"
    rm .env.backup
else
    echo "-> Файл .env уже существует"
fi

echo ""

# Шаг 5: Настройка прав доступа
echo "🔐 Шаг 5: Настройка прав доступа..."
chown -R www-data:www-data $PROJECT_PATH
chmod -R 755 $PROJECT_PATH
chmod -R 775 $PROJECT_PATH/storage
chmod -R 775 $PROJECT_PATH/bootstrap/cache

# Создаем необходимые директории если их нет
mkdir -p $PROJECT_PATH/storage/logs
mkdir -p $PROJECT_PATH/storage/framework/{sessions,views,cache}
mkdir -p $PROJECT_PATH/storage/app/public

chown -R www-data:www-data $PROJECT_PATH/storage
chown -R www-data:www-data $PROJECT_PATH/bootstrap/cache

echo "✅ Права доступа настроены"
echo ""

# Шаг 6: Проверка базы данных
echo "🗄️  Шаг 6: Проверка базы данных..."
echo "Убедитесь, что база данных создана:"
echo ""
echo "mysql -u root -p"
echo "CREATE DATABASE truck_impo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo "CREATE USER 'truck_user'@'localhost' IDENTIFIED BY 'ваш_пароль';"
echo "GRANT ALL PRIVILEGES ON truck_impo.* TO 'truck_user'@'localhost';"
echo "FLUSH PRIVILEGES;"
echo "EXIT;"
echo ""
read -p "База данных настроена? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Настройте базу данных и запустите скрипт снова"
    exit 1
fi

echo ""

# Шаг 7: Миграции
echo "🔄 Шаг 7: Запуск миграций..."
php artisan migrate --force
echo "✅ Миграции выполнены"
echo ""

# Опционально: сидеры
read -p "Запустить сидеры (заполнить тестовыми данными)? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan db:seed --force
    echo "✅ Сидеры выполнены"
fi

echo ""

# Шаг 8: Настройка Nginx
echo "🌐 Шаг 8: Настройка Nginx..."
NGINX_CONFIG="/etc/nginx/sites-available/$PROJECT_NAME"

cat > $NGINX_CONFIG <<'EOF'
server {
    listen 80;
    server_name 155.212.221.20;
    root /var/www/truck-impo-main/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Активация конфигурации
ln -sf $NGINX_CONFIG /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Проверка конфигурации
echo "-> Проверка конфигурации Nginx..."
nginx -t

echo "✅ Nginx настроен"
echo ""

# Шаг 9: Оптимизация Laravel
echo "⚡ Шаг 9: Оптимизация Laravel..."
php artisan optimize:clear
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "✅ Оптимизация выполнена"
echo ""

# Шаг 10: Перезапуск сервисов
echo "🔄 Шаг 10: Перезапуск сервисов..."
systemctl restart nginx
systemctl restart php8.2-fpm

# Проверка статуса
systemctl enable nginx
systemctl enable php8.2-fpm

echo "✅ Сервисы перезапущены"
echo ""

# Опционально: Настройка Supervisor для Horizon
if [ -f "$PROJECT_PATH/artisan" ]; then
    read -p "Настроить Supervisor для Laravel Horizon? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        cat > /etc/supervisor/conf.d/horizon.conf <<EOF
[program:horizon]
process_name=%(program_name)s
command=php $PROJECT_PATH/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=$PROJECT_PATH/storage/logs/horizon.log
stopwaitsecs=3600
EOF
        
        supervisorctl reread
        supervisorctl update
        supervisorctl start horizon
        echo "✅ Horizon настроен и запущен"
    fi
fi

echo ""

# Опционально: Настройка Cron
read -p "Настроить Cron для Laravel Scheduler? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    CRON_CMD="* * * * * cd $PROJECT_PATH && php artisan schedule:run >> /dev/null 2>&1"
    (crontab -l 2>/dev/null | grep -v "schedule:run"; echo "$CRON_CMD") | crontab -
    echo "✅ Cron настроен"
fi

echo ""
echo "=========================================="
echo "✅ ДЕПЛОЙ ЗАВЕРШЕН УСПЕШНО!"
echo "=========================================="
echo ""
echo "📊 Информация:"
echo "   Проект: $PROJECT_PATH"
echo "   URL: http://155.212.221.20"
echo ""
echo "📝 Полезные команды:"
echo "   Логи: tail -f $PROJECT_PATH/storage/logs/laravel.log"
echo "   Обслуживание: php artisan down / php artisan up"
echo "   Обновление: cd $PROJECT_PATH && git pull && composer install"
echo ""
echo "🎉 Откройте в браузере: http://155.212.221.20"
echo ""

