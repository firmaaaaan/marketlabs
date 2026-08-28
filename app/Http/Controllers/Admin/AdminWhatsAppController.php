<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdminWhatsAppController extends Controller
{
    public function index()
    {
        return view('admin.settings.whatsapp', [
            'enabled' => Setting::get('whatsapp_enabled') === '1',
            'number' => Setting::get('whatsapp_number', ''),
            'message' => Setting::get('whatsapp_message', ''),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'whatsapp_enabled' => ['sometimes', 'boolean'],
            'whatsapp_number' => ['nullable', 'string', 'max:20'],
            'whatsapp_message' => ['nullable', 'string', 'max:500'],
        ]);

        // Normalisasi nomor: buang karakter non-digit, awalan 0 → kode negara 62.
        $number = preg_replace('/\D/', '', $validated['whatsapp_number'] ?? '');
        if (str_starts_with($number, '0')) {
            $number = '62'.substr($number, 1);
        }

        if ($request->boolean('whatsapp_enabled') && strlen($number) < 8) {
            return back()
                ->withErrors(['whatsapp_number' => 'Nomor WhatsApp minimal 8 digit (format internasional, contoh: 6281234567890).'])
                ->withInput();
        }

        Setting::set('whatsapp_enabled', $request->boolean('whatsapp_enabled') ? '1' : '0');
        Setting::set('whatsapp_number', $number);
        Setting::set('whatsapp_message', trim($validated['whatsapp_message'] ?? ''));

        return redirect()->route('admin.whatsapp.index')
            ->with('success', 'Pengaturan WhatsApp berhasil diperbarui.');
    }
}
