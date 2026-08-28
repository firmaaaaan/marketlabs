<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user() || ! $request->user()->isSuperAdmin()) {
            return redirect()->route('admin.dashboard')->with('error', 'Anda tidak memiliki akses ke fitur ini.');
        }

        return $next($request);
    }
}
