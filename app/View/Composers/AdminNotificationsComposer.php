<?php

namespace App\View\Composers;

use Illuminate\View\View;

class AdminNotificationsComposer
{
    /**
     * Berikan data notifikasi (dari tabel notifications) ke layout admin.
     */
    public function compose(View $view): void
    {
        $user = auth()->user();

        if (! $user) {
            $view->with('adminUnreadCount', 0);
            $view->with('adminNotifications', collect());

            return;
        }

        $view->with('adminUnreadCount', $user->unreadNotifications()->count());

        $view->with('adminNotifications', $user->notifications()->latest()->limit(8)->get());
    }
}
