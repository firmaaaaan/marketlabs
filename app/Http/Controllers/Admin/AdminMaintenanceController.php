<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdminMaintenanceController extends Controller
{
    public function index()
    {
        return view('admin.settings.maintenance', [
            'enabled' => Setting::get('maintenance_enabled') === '1',
            'message' => Setting::get('maintenance_message', 'Kami sedang melakukan pemeliharaan untuk meningkatkan kualitas layanan. Silakan coba lagi nanti.'),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'maintenance_message' => ['nullable', 'string', 'max:500'],
        ]);

        Setting::set('maintenance_enabled', $request->boolean('maintenance_enabled') ? '1' : '0');
        Setting::set('maintenance_message', $validated['maintenance_message'] ?? '');

        return redirect()->route('admin.maintenance.index')
            ->with('success', 'Pengaturan mode maintenance berhasil disimpan.');
    }
}
