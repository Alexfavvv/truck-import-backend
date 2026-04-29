<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SsoService
{
    private const CACHE_PREFIX = 'sso_code:';

    public function isRedirectUriAllowed(string $uri): bool
    {
        $uri = trim($uri);
        if ($uri === '') {
            return false;
        }

        foreach (config('sso.allowed_redirect_prefixes', []) as $prefix) {
            $prefix = rtrim((string) $prefix, '/');
            if ($prefix === '') {
                continue;
            }
            if ($uri === $prefix || str_starts_with($uri, $prefix.'/')) {
                return true;
            }
        }

        return false;
    }

    public function issueCodeForUser(User $user): string
    {
        $code = Str::random(48);
        $ttl = max(30, (int) config('sso.code_ttl_seconds', 120));

        Cache::put(
            self::CACHE_PREFIX.$code,
            ['user_id' => $user->id],
            now()->addSeconds($ttl)
        );

        return $code;
    }

    public function redeemCode(string $code): ?User
    {
        $code = trim($code);
        if (strlen($code) < 32) {
            return null;
        }

        $payload = Cache::pull(self::CACHE_PREFIX.$code);
        if (! is_array($payload) || empty($payload['user_id'])) {
            return null;
        }

        return User::query()->find($payload['user_id']);
    }
}
