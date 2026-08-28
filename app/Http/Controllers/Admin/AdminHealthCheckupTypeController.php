<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HealthTestType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminHealthCheckupTypeController extends Controller
{
    public function index()
    {
        $types = HealthTestType::withCount('checkups')->orderBy('name')->get();

        return view('admin.health-checkup-types.index', compact('types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_-]+$/', 'unique:health_test_types,key'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        HealthTestType::create([
            'key' => $validated['key'],
            'name' => $validated['name'],
            'description' => trim((string) ($validated['description'] ?? '')) !== '' ? trim($validated['description']) : null,
            'price' => $validated['price'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.health-checkup-types.index')
            ->with('success', "Jenis pemeriksaan '{$validated['name']}' berhasil ditambahkan.");
    }

    public function update(Request $request, HealthTestType $healthCheckupType)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_-]+$/', Rule::unique('health_test_types', 'key')->ignore($healthCheckupType->id)],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $healthCheckupType->update([
            'key' => $validated['key'],
            'name' => $validated['name'],
            'description' => trim((string) ($validated['description'] ?? '')) !== '' ? trim($validated['description']) : null,
            'price' => $validated['price'],
            'is_active' => $request->boolean('is_active', $healthCheckupType->is_active),
        ]);

        return redirect()->route('admin.health-checkup-types.index')
            ->with('success', "Jenis pemeriksaan '{$validated['name']}' berhasil diperbarui.");
    }

    public function destroy(HealthTestType $healthCheckupType)
    {
        if ($healthCheckupType->checkups()->count() > 0) {
            return back()->with('error', "Jenis pemeriksaan '{$healthCheckupType->name}' masih dipakai oleh booking dan tidak dapat dihapus.");
        }

        $healthCheckupType->delete();

        return redirect()->route('admin.health-checkup-types.index')
            ->with('success', "Jenis pemeriksaan '{$healthCheckupType->name}' berhasil dihapus.");
    }
}
