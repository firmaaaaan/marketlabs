<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SampleForm;
use App\Models\SampleType;
use Illuminate\Http\Request;

class AdminSampleAttributeController extends Controller
{
    public function index()
    {
        $forms = SampleForm::withCount('items')->orderBy('name')->get();
        $types = SampleType::withCount('items')->orderBy('name')->get();

        return view('admin.sample-attributes.index', compact('forms', 'types'));
    }

    public function storeForm(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        SampleForm::firstOrCreate(['name' => trim($validated['name'])], ['is_active' => true]);

        return back()->with('success', "Bentuk sampel '{$validated['name']}' berhasil ditambahkan.");
    }

    public function updateForm(Request $request, SampleForm $sampleForm)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $sampleForm->update([
            'name' => trim($validated['name']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', "Bentuk sampel '{$sampleForm->name}' berhasil diperbarui.");
    }

    public function destroyForm(SampleForm $sampleForm)
    {
        if ($sampleForm->items()->exists()) {
            return back()->with('error', "Bentuk '{$sampleForm->name}' masih dipakai oleh pengujian dan tidak dapat dihapus.");
        }

        $sampleForm->delete();

        return back()->with('success', "Bentuk sampel '{$sampleForm->name}' berhasil dihapus.");
    }

    public function storeType(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        SampleType::firstOrCreate(['name' => trim($validated['name'])], ['is_active' => true]);

        return back()->with('success', "Jenis sampel '{$validated['name']}' berhasil ditambahkan.");
    }

    public function updateType(Request $request, SampleType $sampleType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $sampleType->update([
            'name' => trim($validated['name']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', "Jenis sampel '{$sampleType->name}' berhasil diperbarui.");
    }

    public function destroyType(SampleType $sampleType)
    {
        if ($sampleType->items()->exists()) {
            return back()->with('error', "Jenis '{$sampleType->name}' masih dipakai oleh pengujian dan tidak dapat dihapus.");
        }

        $sampleType->delete();

        return back()->with('success', "Jenis sampel '{$sampleType->name}' berhasil dihapus.");
    }
}
