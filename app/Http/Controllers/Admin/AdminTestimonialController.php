<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class AdminTestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::orderByDesc('created_at')->get();

        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'max:150'],
            'quote' => ['required', 'string', 'max:1000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        Testimonial::create([
            'name' => $validated['name'],
            'role' => trim((string) ($validated['role'] ?? '')) !== '' ? trim($validated['role']) : null,
            'quote' => $validated['quote'],
            'rating' => (int) $validated['rating'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.testimonials.index')
            ->with('success', "Testimoni '{$validated['name']}' berhasil ditambahkan.");
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'max:150'],
            'quote' => ['required', 'string', 'max:1000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $testimonial->update([
            'name' => $validated['name'],
            'role' => trim((string) ($validated['role'] ?? '')) !== '' ? trim($validated['role']) : null,
            'quote' => $validated['quote'],
            'rating' => (int) $validated['rating'],
            'is_active' => $request->boolean('is_active', $testimonial->is_active),
        ]);

        return redirect()->route('admin.testimonials.index')
            ->with('success', "Testimoni '{$validated['name']}' berhasil diperbarui.");
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')
            ->with('success', "Testimoni '{$testimonial->name}' berhasil dihapus.");
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:testimonials,id'],
        ]);

        $count = Testimonial::whereIn('id', $validated['ids'])->delete();

        return redirect()->route('admin.testimonials.index')
            ->with('success', "{$count} testimoni berhasil dihapus.");
    }
}
