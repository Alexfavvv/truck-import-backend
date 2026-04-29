<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Разрешённые префиксы redirect_uri (SSO → Next.js)
    |--------------------------------------------------------------------------
    |
    | URL возврата после /sso/authorize должен начинаться с одного из префиксов.
    | Пример: https://truck-import.ru/auth/callback при префиксе https://truck-import.ru
    |
    | Переменная окружения: SSO_ALLOWED_REDIRECT_PREFIXES (через запятую)
    |
    */

    'allowed_redirect_prefixes' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('SSO_ALLOWED_REDIRECT_PREFIXES', ''))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Время жизни одноразового кода (секунды)
    |--------------------------------------------------------------------------
    */

    'code_ttl_seconds' => (int) env('SSO_CODE_TTL', 120),

];
