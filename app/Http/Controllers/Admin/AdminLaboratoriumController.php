<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laboratorium;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminLaboratoriumController extends Controller
{
    public function index()
    {
        $laboratoriums = Laboratorium::withCount('researchProposals')->orderBy('name')->get();

        return view('admin.laboratoriums.index', compact('laboratoriums'));
    }

    public function create()
    {
        return view('admin.laboratoriums.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:laboratoriums,code'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        Laboratorium::create($validated + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.laboratoriums.index')
            ->with('success', "Laboratorium '{$validated['name']}' berhasil ditambahkan.");
    }

    public function edit(Laboratorium $laboratorium)
    {
        return view('admin.laboratoriums.edit', compact('laboratorium'));
    }

    public function update(Request $request, Laboratorium $laboratorium)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('laboratoriums', 'code')->ignore($laboratorium->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $laboratorium->update($validated + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.laboratoriums.index')
            ->with('success', "Laboratorium '{$validated['name']}' berhasil diperbarui.");
    }

    public function destroy(Laboratorium $laboratorium)
    {
        if ($laboratorium->researchProposals()->count() > 0) {
            return back()->with('error', "Laboratorium '{$laboratorium->name}' masih dipakai oleh permohonan riset dan tidak dapat dihapus.");
        }

        $laboratorium->delete();

        return redirect()->route('admin.laboratoriums.index')
            ->with('success', "Laboratorium '{$laboratorium->name}' berhasil dihapus.");
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:laboratoriums,id'],
        ]);

        $labs = Laboratorium::whereIn('id', $validated['ids'])->get();
        $deleted = 0;
        $skipped = [];

        foreach ($labs as $lab) {
            if ($lab->researchProposals()->count() > 0) {
                $skipped[] = $lab->name;
                continue;
            }
            $lab->delete();
            $deleted++;
        }

        $message = "{$deleted} laboratorium berhasil dihapus.";
        if (! empty($skipped)) {
            $message .= ' ' . count($skipped) . ' dilewati karena masih dipakai: ' . implode(', ', $skipped) . '.';
        }

        return redirect()->route('admin.laboratoriums.index')->with('success', $message);
    }
}
