<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Редирект HTTP → HTTPS, чтобы форма логина и другие данные не отправлялись по незащищённому соединению.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->secure()) {
            return $next($request);
        }

        if (! str_starts_with(config('app.url'), 'https://')) {
            return $next($request);
        }

        return redirect()->secure($request->getRequestUri(), 301);
    }
}
