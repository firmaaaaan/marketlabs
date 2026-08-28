<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiting\Unlimited;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Batasi request tulis (POST/PUT/PATCH/DELETE) pada grup terautentikasi.
 * Request baca (GET/HEAD/OPTIONS) tidak ikut dihitung.
 */
class ThrottleMutations
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $next($request);
        }

        $limiter = RateLimiter::limiter('mutations');
        $limit = $limiter($request);

        if ($limit instanceof Unlimited) {
            return $next($request);
        }

        $key = 'mutations:'.$limit->key;

        if (RateLimiter::tooManyAttempts($key, $limit->maxAttempts)) {
            return response(
                $request->expectsJson()
                    ? ['message' => 'Terlalu banyak permintaan. Silakan coba lagi nanti.']
                    : 'Terlalu banyak permintaan. Silakan coba lagi nanti.',
                429,
                ['Retry-After' => RateLimiter::availableIn($key)],
            );
        }

        RateLimiter::hit($key, $limit->decaySeconds);

        return $next($request);
    }
}