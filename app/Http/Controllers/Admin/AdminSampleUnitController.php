<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SampleUnit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminSampleUnitController extends Controller
{
    public function index()
    {
        $units = SampleUnit::withCount('parameters')->orderBy('name')->get();

        return view('admin.sample-units.index', compact('units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:sample_units,name'],
            'symbol' => ['nullable', 'string', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        SampleUnit::create([
            'name' => $validated['name'],
            'symbol' => trim((string) ($validated['symbol'] ?? '')) !== '' ? trim($validated['symbol']) : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.sample-units.index')
            ->with('success', "Satuan '{$validated['name']}' berhasil ditambahkan.");
    }

    public function update(Request $request, SampleUnit $sampleUnit)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('sample_units', 'name')->ignore($sampleUnit->id)],
            'symbol' => ['nullable', 'string', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $sampleUnit->update([
            'name' => $validated['name'],
            'symbol' => trim((string) ($validated['symbol'] ?? '')) !== '' ? trim($validated['symbol']) : null,
            'is_active' => $request->boolean('is_active', $sampleUnit->is_active),
        ]);

        return redirect()->route('admin.sample-units.index')
            ->with('success', "Satuan '{$validated['name']}' berhasil diperbarui.");
    }

    public function destroy(SampleUnit $sampleUnit)
    {
        if ($sampleUnit->parameters()->count() > 0) {
            return back()->with('error', "Satuan '{$sampleUnit->name}' masih dipakai oleh parameter dan tidak dapat dihapus.");
        }

        $sampleUnit->delete();

        return redirect()->route('admin.sample-units.index')
            ->with('success', "Satuan '{$sampleUnit->name}' berhasil dihapus.");
    }
}
