<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cookie\CookieJar;

class EncryptCookies
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        foreach ($response->headers->getCookies() as $cookie) {
            $cookie->setValue(encrypt($cookie->getValue()));
        }

        return $response;
    }
}
