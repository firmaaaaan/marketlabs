<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * JSON feed notifikasi untuk user (dipakai polling realtime).
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        $items = $user->notifications()
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'title' => $n->data['title'] ?? 'Notifikasi',
                'message' => $n->data['message'] ?? null,
                'url' => $n->data['url'] ?? null,
                'time' => $n->created_at->diffForHumans(),
                'is_read' => $n->read_at !== null,
            ]);

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'items' => $items,
        ]);
    }

    /**
     * Halaman semua notifikasi user.
     * Laboran memakai layout staff; user biasa memakai layout publik.
     */
    public function all(): View
    {
        $notifications = auth()->user()->notifications()->latest()->paginate(15);

        $user = auth()->user();

        if ($user->isLaboran()) {
            return view('staff.notifications.index', compact('notifications'));
        }

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Tandai semua notifikasi user sebagai dibaca.
     * Dipanggil via AJAX (lonceng) atau form biasa (tombol "Tandai Semua Dibaca").
     * Memakai query langsung (bukan relasi yang bisa ter-cache) agar selalu up-to-date.
     */
    public function markRead(Request $request): JsonResponse|RedirectResponse
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    /**
     * Hapus notifikasi secara bulk berdasarkan ID yang dipilih.
     */
    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['string'],
        ]);

        $count = auth()->user()->notifications()->whereIn('id', $request->ids)->delete();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'deleted' => $count]);
        }

        return back()->with('success', "{$count} notifikasi berhasil dihapus.");
    }
}
