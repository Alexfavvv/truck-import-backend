

# 🚀 Запуск Truck-Import

### Настройка `.env`

-   Скопируй `.env.example` в `.env`.

-   **Общие настройки:**
    ```dotenv
    APP_ENV=production
    APP_DEBUG=false
    APP_URL=[https://your-domain.com](https://your-domain.com) # Ваш домен на продакшене
    ```

-   **Настройки базы данных:**
    ```dotenv
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1 # IP или hostname вашего PostgreSQL-сервера
    DB_PORT=5432
    DB_DATABASE=truck_import_prod
    DB_USERNAME=your_db_user
    DB_PASSWORD=your_db_password
    ```

-   **Настройки Redis:**
    ```dotenv
    CACHE_DRIVER=redis
    SESSION_DRIVER=redis
    QUEUE_CONNECTION=redis
    REDIS_CLIENT=phpredis
    REDIS_HOST=127.0.0.1 # IP или hostname вашего Redis-сервера
    REDIS_PASSWORD=null # Или ваш пароль Redis
    REDIS_PORT=6379
    ```
---

## Деплой на продакшен

Этот раздел описывает шаги, необходимые для развертывания приложения в продакшн-окружении. Предполагается наличие установленного PHP (версии 8.2+), Composer, Node.js, npm, PostgreSQL, Redis, Nginx (или Apache) и Supervisor.

1.  **Клонирование репозитория:**
    ```bash
    git clone https://github.com/pkmStudio/truck-impo.git
    cd truck-impo
    ```

2.  **Настройка `.env`:**
    Скопируйте `.env.example` в `.env` и настройте переменные согласно продакшн-окружению (см. раздел "Настройка `.env`" выше).


3.  **Установка зависимостей:**
    ```bash
    composer install --no-dev --optimize-autoloader
    npm install
    npm run build # Сборка фронтенда для продакшена
    ```

4.  **Настройка прав доступа к директориям:**
    ```bash
    sudo chown -R www-data:www-data storage bootstrap/cache # Или другой пользователь веб-сервера
    sudo chmod -R 775 storage bootstrap/cache
    ```

5.  **Запуск миграций базы данных:**
    ```bash
    php artisan migrate --force
    # php artisan db:seed # Если нужно запустить сидеры на продакшене
    ```

6.  **Оптимизация Laravel:**
    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    # php artisan event:cache # кэширование событий
    ```

7.  **Настройка и запуск фоновых процессов (Supervisor):**
    Установите Supervisor (например, `sudo apt install supervisor` на Ubuntu) и настройте его для запуска следующих процессов как демонов:

    * **Laravel Horizon:**
      Создайте конфигурационный файл для Horizon (например, `/etc/supervisor/conf.d/horizon.conf`):
        ```ini
        [program:horizon]
        process_name=%(program_name)s
        command=php /path/to/your/app/artisan horizon
        autostart=true
        autorestart=true
        user=www-data # Или пользователь, от которого работает ваше приложение
        numprocs=1
        redirect_stderr=true
        stdout_logfile=/path/to/your/app/storage/logs/horizon.log
        stopwaitsecs=3600 # Дать время заданиям завершиться
        ```

    После создания файлов обновите Supervisor:
    ```bash
    sudo supervisorctl reread
    sudo supervisorctl update
    sudo supervisorctl start horizon reverb
    ```

8.  **Настройка веб-сервера (Nginx/Apache):**
    Настройте ваш веб-сервер для обслуживания директории `public` вашего Laravel-приложения и для проксирования WebSocket-соединений на Reverb (порт 8080).

    *Пример для Nginx:*
    ```nginx
    server {
        listen 80;
        listen [::]:80;
        server_name your-domain.com www.your-domain.com;
        return 301 https://$host$request_uri;
    }

    server {
        listen 443 ssl http2;
        listen [::]:443 ssl http2;
        server_name your-domain.com www.your-domain.com;
        root /path/to/your/app/public; # Путь к директории public

        index index.php index.html index.htm;

        # ... (Стандартная настройка для Laravel) ...

        # Конфигурация для WebSockets (Reverb)
        location /app {
            proxy_pass http://127.0.0.1:8080; # Или IP-адрес сервера Reverb
            proxy_http_version 1.1;
            proxy_set_header Upgrade $http_upgrade;
            proxy_set_header Connection "Upgrade";
            proxy_set_header Host $host;
            proxy_read_timeout 86400s; # Или подходящее значение
        }
    }
    ```
    *При использовании Reverb с `BROADCAST_DRIVER=reverb`, он по умолчанию использует endpoint `/app` для WebSocket-соединений.*



## Локальная разработка (Можно через Sail)

###  Клонирование репозитория
```bash
git clone https://github.com/pkmStudio/truck-impo.git
cd truck-impo
```


### Установка зависимостей
```bash
composer install
npm install
```

### Запуск миграций и сидеров
```bash
php artisan migrate --seed
```


### Запуск Vite
```bash
npm run build
```


### Запуск локального сервера
```bash
php artisan serve
```


## Создание администратора вручную
```bash
php artisan tinker
```

```php
use Illuminate\Support\Facades\Hash;
use App\Models\User;

User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => Hash::make('password123'),
    'is_admin' => true,
]);
```


###  Готово!
-   Зайди в админку: `http://localhost:8000/admin/login`

-   Введи `admin@example.com` / `password123`
