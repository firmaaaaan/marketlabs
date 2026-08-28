<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ToolCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCategoryController extends Controller
{
    public function index()
    {
        $categories = ToolCategory::withCount('tools')->orderBy('name')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:tool_categories,name'],
        ]);

        ToolCategory::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', "Kategori '{$validated['name']}' berhasil ditambahkan.");
    }

    public function update(Request $request, ToolCategory $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('tool_categories', 'name')->ignore($category->id)],
        ]);

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', "Kategori '{$validated['name']}' berhasil diperbarui.");
    }

    public function destroy(ToolCategory $category)
    {
        if ($category->tools()->count() > 0) {
            return back()->with('error', "Kategori '{$category->name}' masih dipakai oleh alat dan tidak dapat dihapus.");
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', "Kategori '{$category->name}' berhasil dihapus.");
    }
}
