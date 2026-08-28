<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mitra;
use Illuminate\Http\Request;

class AdminMitrasController extends Controller
{
    public function index()
    {
        $mitras = Mitra::orderBy('sort_order')->orderByDesc('created_at')->get();

        return view('admin.mitras.index', compact('mitras'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'logo' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'url', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        Mitra::create([
            'name' => $validated['name'],
            'logo' => $validated['logo'] ?? null,
            'website' => $validated['website'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return redirect()->route('admin.mitras.index')
            ->with('success', "Mitra '{$validated['name']}' berhasil ditambahkan.");
    }

    public function update(Request $request, Mitra $mitra)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'logo' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'url', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $mitra->update([
            'name' => $validated['name'],
            'logo' => $validated['logo'] ?? null,
            'website' => $validated['website'] ?? null,
            'is_active' => $request->boolean('is_active', $mitra->is_active),
            'sort_order' => (int) ($validated['sort_order'] ?? $mitra->sort_order),
        ]);

        return redirect()->route('admin.mitras.index')
            ->with('success', "Mitra '{$validated['name']}' berhasil diperbarui.");
    }

    public function destroy(Mitra $mitra)
    {
        $mitra->delete();

        return redirect()->route('admin.mitras.index')
            ->with('success', "Mitra '{$mitra->name}' berhasil dihapus.");
    }
}
