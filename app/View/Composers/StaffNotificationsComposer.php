<?php

namespace App\View\Composers;

use Illuminate\View\View;

class StaffNotificationsComposer
{
    /**
     * Berikan data notifikasi (dari tabel notifications) ke layout staff (laboran).
     */
    public function compose(View $view): void
    {
        $user = auth()->user();

        if (! $user) {
            $view->with('staffUnreadCount', 0);
            $view->with('staffNotifications', collect());

            return;
        }

        $view->with('staffUnreadCount', $user->unreadNotifications()->count());

        $view->with('staffNotifications', $user->notifications()->latest()->limit(8)->get());
    }
}
