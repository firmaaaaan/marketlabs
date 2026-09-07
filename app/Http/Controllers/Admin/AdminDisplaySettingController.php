<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdminDisplaySettingController extends Controller
{
    public function index()
    {
        return view('admin.settings.display', [
            'featuredToolsCount' => (int) Setting::get('landing_featured_tools_count', 5),
            'featuredParametersCount' => (int) Setting::get('landing_featured_parameters_count', 5),
            'showAllTools' => Setting::get('landing_show_all_tools') === '1',
            'showAllParameters' => Setting::get('landing_show_all_parameters') === '1',
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'landing_featured_tools_count' => ['required', 'integer', 'min:1', 'max:50'],
            'landing_featured_parameters_count' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        Setting::set('landing_featured_tools_count', (string) $validated['landing_featured_tools_count']);
        Setting::set('landing_featured_parameters_count', (string) $validated['landing_featured_parameters_count']);
        Setting::set('landing_show_all_tools', $request->boolean('landing_show_all_tools') ? '1' : '0');
        Setting::set('landing_show_all_parameters', $request->boolean('landing_show_all_parameters') ? '1' : '0');

        return redirect()->route('admin.display-settings.index')
            ->with('success', 'Pengaturan jumlah card katalog berhasil disimpan.');
    }
}
