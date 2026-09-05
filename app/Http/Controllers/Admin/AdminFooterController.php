<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FooterLogo;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminFooterController extends Controller
{
    public function index()
    {
        $logos = FooterLogo::ordered()->get();

        $address = Setting::get('footer_address', 'Jl. Laboratorium Teknologi No. 123, Bandung, Jawa Barat');
        $phone = Setting::get('footer_phone', '+6281234567890');
        $email = Setting::get('footer_email', 'info@marketlabs.id');

        return view('admin.footer.index', compact('logos', 'address', 'phone', 'email'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'footer_address' => ['required', 'string', 'max:500'],
            'footer_phone' => ['nullable', 'string', 'max:20'],
            'footer_email' => ['nullable', 'email', 'max:255'],
        ]);

        Setting::set('footer_address', trim($validated['footer_address']));
        Setting::set('footer_phone', trim($validated['footer_phone'] ?? ''));
        Setting::set('footer_email', trim($validated['footer_email'] ?? ''));

        return redirect()->route('admin.footer.index')
            ->with('success', 'Alamat dan kontak footer berhasil disimpan.');
    }

    public function storeLogo(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'url' => ['nullable', 'url', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        FooterLogo::create([
            'name' => $validated['name'],
            'image' => $request->file('image')->store('footer-logos', 'public'),
            'url' => trim($validated['url'] ?? '') ?: null,
            'sort_order' => (int) (FooterLogo::max('sort_order') ?? -1) + 1,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.footer.index')
            ->with('success', "Logo '{$validated['name']}' berhasil ditambahkan.");
    }

    public function updateLogo(Request $request, FooterLogo $logo)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'url' => ['nullable', 'url', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            if ($logo->image) {
                Storage::disk('public')->delete($logo->image);
            }
            $validated['image'] = $request->file('image')->store('footer-logos', 'public');
        }

        $logo->update([
            'name' => $validated['name'],
            'image' => $validated['image'] ?? $logo->image,
            'url' => trim($validated['url'] ?? '') ?: null,
            'is_active' => $request->boolean('is_active', $logo->is_active),
        ]);

        return redirect()->route('admin.footer.index')
            ->with('success', "Logo '{$validated['name']}' berhasil diperbarui.");
    }

    public function destroyLogo(FooterLogo $logo)
    {
        if ($logo->image) {
            Storage::disk('public')->delete($logo->image);
        }

        $logo->delete();

        return redirect()->route('admin.footer.index')
            ->with('success', "Logo '{$logo->name}' berhasil dihapus.");
    }

    public function moveLogo(FooterLogo $logo, string $direction)
    {
        $neighbor = $direction === 'up'
            ? FooterLogo::where('sort_order', '<', $logo->sort_order)->orderByDesc('sort_order')->first()
            : FooterLogo::where('sort_order', '>', $logo->sort_order)->orderBy('sort_order')->first();

        if ($neighbor) {
            [$logo->sort_order, $neighbor->sort_order] = [$neighbor->sort_order, $logo->sort_order];
            $logo->save();
            $neighbor->save();
        }

        return redirect()->route('admin.footer.index')
            ->with('success', "Urutan logo '{$logo->name}' diperbarui.");
    }
}
