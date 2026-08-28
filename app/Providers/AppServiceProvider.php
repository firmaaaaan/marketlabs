<?php

namespace App\Providers;

use App\View\Composers\AdminNotificationsComposer;
use App\View\Composers\ClientNotificationsComposer;
use App\View\Composers\StaffNotificationsComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Paksa locale Indonesia agar pesan validasi, waktu relatif, dan format tanggal (translatedFormat) berbahasa Indonesia.
        App::setLocale('id');
        Carbon::setLocale('id');

        View::composer('layouts.admin', AdminNotificationsComposer::class);
        View::composer('layouts.app', ClientNotificationsComposer::class);
        View::composer('layouts.staff', StaffNotificationsComposer::class);

        $this->configureRateLimiting();
    }

    /**
     * Konfigurasi named rate limiters yang dipakai di seluruh aplikasi.
     */
    protected function configureRateLimiting(): void
    {
        if (App::environment('testing')) {
            foreach (['auth', 'register', 'contact', 'public', 'search', 'files', 'mutations', 'admin-ops'] as $name) {
                RateLimiter::for($name, fn () => Limit::none());
            }

            return;
        }

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('public', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(15)->by($request->user()?->id ?? $request->ip());
        });

        RateLimiter::for('files', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?? $request->ip());
        });

        RateLimiter::for('mutations', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?? $request->ip());
        });

        RateLimiter::for('admin-ops', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?? $request->ip());
        });
    }
}
