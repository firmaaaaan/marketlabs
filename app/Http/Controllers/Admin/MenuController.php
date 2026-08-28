<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingPageSection;
use App\Models\MenuItem;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    public function index()
    {
        $sidebarItems = MenuItem::sidebar()->ordered()->get();
        $topbarItems = MenuItem::topbar()->ordered()->get();
        $sections = LandingPageSection::ordered()->get();
        $logo = Setting::get('site_logo', '');
        $favicon = Setting::get('site_favicon', '');

        return view('admin.menus.index', compact('sidebarItems', 'topbarItems', 'sections', 'logo', 'favicon'));
    }

    public function updateBranding(Request $request)
    {
        $validated = $request->validate([
            'logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:ico,png,jpg,jpeg,webp', 'max:2048'],
        ]);

        $logo = Setting::get('site_logo', '');
        if ($request->hasFile('logo')) {
            if ($logo) {
                Storage::disk('public')->delete($logo);
            }
            $logo = $request->file('logo')->store('branding', 'public');
        } elseif ($request->boolean('remove_logo')) {
            if ($logo) {
                Storage::disk('public')->delete($logo);
            }
            $logo = '';
        }
        Setting::set('site_logo', $logo);

        $favicon = Setting::get('site_favicon', '');
        if ($request->hasFile('favicon')) {
            if ($favicon) {
                Storage::disk('public')->delete($favicon);
            }
            $favicon = $request->file('favicon')->store('branding', 'public');
        } elseif ($request->boolean('remove_favicon')) {
            if ($favicon) {
                Storage::disk('public')->delete($favicon);
            }
            $favicon = '';
        }
        Setting::set('site_favicon', $favicon);

        return redirect()->route('admin.menus.index')
            ->with('success', 'Logo dan favicon berhasil disimpan.');
    }

    public function storeMenuItem(Request $request)
    {
        $validated = $request->validate([
            'group' => ['required', 'string', 'in:sidebar,topbar'],
            'label' => ['required', 'string', 'max:100'],
            'route_name' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        MenuItem::create($validated);

        return back()->with('success', 'Menu item berhasil ditambahkan.');
    }

    public function updateMenuItem(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'route_name' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $menuItem->update($validated);

        return back()->with('success', 'Menu item berhasil diperbarui.');
    }

    public function destroyMenuItem(MenuItem $menuItem)
    {
        $menuItem->delete();

        return back()->with('success', 'Menu item berhasil dihapus.');
    }

    public function sortMenuItem(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*' => ['required', 'integer', 'exists:menu_items,id'],
        ]);

        foreach ($validated['items'] as $index => $id) {
            MenuItem::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    public function toggleMenuItem(MenuItem $menuItem)
    {
        $menuItem->update(['is_active' => ! $menuItem->is_active]);

        return back()->with('success', 'Status menu item berhasil diubah.');
    }

    // Landing Page Sections

    public function updateSection(Request $request, LandingPageSection $section)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $section->update($validated);

        return back()->with('success', 'Section berhasil diperbarui.');
    }

    public function toggleSection(LandingPageSection $section)
    {
        $section->update(['is_active' => ! $section->is_active]);

        return back()->with('success', 'Status section berhasil diubah.');
    }

    public function sortSection(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*' => ['required', 'integer', 'exists:landing_page_sections,id'],
        ]);

        foreach ($validated['items'] as $index => $id) {
            LandingPageSection::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
