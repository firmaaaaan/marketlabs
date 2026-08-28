<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureProfileIsComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && ! $user->isAdmin() && ! $user->isLaboran() && ! $user->isProfileComplete()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Profil Anda belum lengkap.'], 403);
            }

            return redirect()->route('profile.complete')
                ->with('warning', 'Silakan lengkapi informasi akun Anda terlebih dahulu.');
        }

        return $next($request);
    }
}
