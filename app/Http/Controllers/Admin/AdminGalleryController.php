<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminGalleryController extends Controller
{
    public function index()
    {
        $images = GalleryImage::orderBy('sort_order')->orderByDesc('created_at')->get();

        return view('admin.gallery.index', compact('images'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'images' => ['required', 'array', 'max:20'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'title' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
        ]);

        $maxOrder = GalleryImage::max('sort_order') ?? 0;

        foreach ($request->file('images') as $file) {
            $path = $file->store('gallery', 'public');
            GalleryImage::create([
                'title' => $validated['title'] ?? null,
                'caption' => $validated['caption'] ?? null,
                'path' => $path,
                'sort_order' => ++$maxOrder,
                'is_active' => true,
            ]);
        }

        return redirect()->route('admin.gallery.index')
            ->with('success', count($request->file('images')) . ' gambar berhasil ditambahkan.');
    }

    public function update(Request $request, GalleryImage $image)
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $image->update([
            'title' => $validated['title'] ?? $image->title,
            'caption' => $validated['caption'] ?? $image->caption,
            'is_active' => $request->boolean('is_active', $image->is_active),
        ]);

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Gambar berhasil diperbarui.');
    }

    public function destroy(GalleryImage $image)
    {
        Storage::disk('public')->delete($image->path);
        $image->delete();

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Gambar berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['string', 'exists:gallery_images,id'],
        ]);

        $images = GalleryImage::whereIn('id', $validated['ids'])->get();

        foreach ($images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        return redirect()->route('admin.gallery.index')
            ->with('success', count($images) . ' gambar berhasil dihapus.');
    }

    public function sort(Request $request)
    {
        $order = $request->input('order', []);

        foreach ($order as $position => $id) {
            GalleryImage::where('id', $id)->update(['sort_order' => $position]);
        }

        return response()->json(['ok' => true]);
    }
}
