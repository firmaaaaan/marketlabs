<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsLaboran
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user() || ! $request->user()->isLaboran()) {
            return redirect()->route('home')->with('error', 'Anda tidak memiliki akses ke halaman laboran.');
        }

        return $next($request);
    }
}
