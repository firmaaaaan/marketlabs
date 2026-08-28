<?php

namespace App\View\Composers;

use Illuminate\View\View;

class ClientNotificationsComposer
{
    /**
     * Berikan data notifikasi untuk user yang sedang login ke layout publik.
     */
    public function compose(View $view): void
    {
        $user = auth()->user();

        if (! $user) {
            $view->with('clientUnreadCount', 0);
            $view->with('clientNotifications', collect());

            return;
        }

        $view->with('clientUnreadCount', $user->unreadNotifications()->count());

        $view->with('clientNotifications', $user->notifications()->latest()->limit(10)->get());
    }
}
