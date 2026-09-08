<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;

class MaintenanceMode
{
    public function handle(Request $request, Closure $next)
    {
        if (Setting::get('maintenance_enabled') === '1') {
            $user = $request->user();

            // Superadmin bypass
            if ($user && $user->isSuperAdmin()) {
                return $next($request);
            }

            return redirect()->route('maintenance');
        }

        return $next($request);
    }
}
