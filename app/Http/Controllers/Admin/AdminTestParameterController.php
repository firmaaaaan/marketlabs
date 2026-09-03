<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SampleUnit;
use App\Models\TestParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminTestParameterController extends Controller
{
    public function index(Request $request)
    {
        $query = TestParameter::with('unit')->latest();

        if ($search = $request->query('search')) {
            $escaped = addcslashes($search, '%_');
            $query->where('name', 'like', "%{$escaped}%");
        }

        if ($unitId = $request->query('unit_id')) {
            $query->where('unit_id', $unitId);
        }

        $parameters = $query->paginate(15)->withQueryString();
        $units = SampleUnit::orderBy('name')->get();

        return view('admin.test-parameters.index', compact('parameters', 'units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'method' => ['nullable', 'string', 'max:255'],
            'unit_id' => ['required', 'exists:sample_units,id'],
            'rate' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        TestParameter::create([
            'name' => $validated['name'],
            'method' => trim((string) ($validated['method'] ?? '')) !== '' ? trim($validated['method']) : null,
            'unit_id' => $validated['unit_id'],
            'rate' => $validated['rate'],
            'description' => trim((string) ($validated['description'] ?? '')) !== '' ? trim($validated['description']) : null,
            'image' => $this->storeImage($request),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.test-parameters.index')
            ->with('success', "Parameter '{$validated['name']}' berhasil ditambahkan.");
    }

    public function update(Request $request, TestParameter $testParameter)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'method' => ['nullable', 'string', 'max:255'],
            'unit_id' => ['required', 'exists:sample_units,id'],
            'rate' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $testParameter->update([
            'name' => $validated['name'],
            'method' => trim((string) ($validated['method'] ?? '')) !== '' ? trim($validated['method']) : null,
            'unit_id' => $validated['unit_id'],
            'rate' => $validated['rate'],
            'description' => trim((string) ($validated['description'] ?? '')) !== '' ? trim($validated['description']) : null,
            'image' => $this->storeImage($request, $testParameter),
            'is_active' => $request->boolean('is_active', $testParameter->is_active),
        ]);

        return redirect()->route('admin.test-parameters.index')
            ->with('success', "Parameter '{$validated['name']}' berhasil diperbarui.");
    }

    public function toggleActive(TestParameter $testParameter)
    {
        $testParameter->update(['is_active' => ! $testParameter->is_active]);

        return back()->with('success', "Parameter '{$testParameter->name}' kini " . ($testParameter->is_active ? 'aktif' : 'nonaktif') . ".");
    }

    public function destroy(TestParameter $testParameter)
    {
        if ($testParameter->sampleTestItems()->exists()) {
            return back()->with('error', "Parameter '{$testParameter->name}' masih dipakai oleh pengujian dan tidak dapat dihapus.");
        }

        $testParameter->delete();

        return redirect()->route('admin.test-parameters.index')
            ->with('success', "Parameter '{$testParameter->name}' berhasil dihapus.");
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['string', 'exists:test_parameters,id'],
        ]);

        $parameters = TestParameter::whereIn('id', $validated['ids'])->get();
        $deleted = 0;
        $skipped = [];

        foreach ($parameters as $parameter) {
            if ($parameter->sampleTestItems()->exists()) {
                $skipped[] = $parameter->name;
                continue;
            }
            $parameter->delete();
            $deleted++;
        }

        $message = "{$deleted} parameter berhasil dihapus.";
        if (! empty($skipped)) {
            $message .= ' ' . count($skipped) . ' dilewati karena masih dipakai: ' . implode(', ', $skipped) . '.';
        }

        return redirect()->route('admin.test-parameters.index')->with('success', $message);
    }

    protected function storeImage(Request $request, ?TestParameter $parameter = null): ?string
    {
        if (! $request->hasFile('image')) {
            return $parameter?->image;
        }

        if ($parameter?->image) {
            Storage::disk('public')->delete($parameter->image);
        }

        return $request->file('image')->store('test-parameters', 'public');
    }
}
