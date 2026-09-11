<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BenchFeeLevel;
use App\Models\BenchFeeRate;
use App\Models\ResearchProposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminBenchFeeLevelController extends Controller
{
    public function index()
    {
        $levels = BenchFeeLevel::ordered()->get();

        return view('admin.bench-fee.index', compact('levels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        // Generate name from label (slug).
        $name = strtoupper($validated['label']);
        $name = preg_replace('/[^A-Z0-9\/]/', '', $name);

        if (BenchFeeLevel::where('name', $name)->exists()) {
            return back()->with('error', 'Jenjang "'.$validated['label'].'" sudah ada.');
        }

        DB::transaction(function () use ($name, $validated) {
            $level = BenchFeeLevel::create([
                'name' => $name,
                'label' => $validated['label'],
                'sort_order' => $validated['sort_order'],
            ]);

            // Buat rate default untuk semua type × category.
            $categories = array_keys(ResearchProposal::benchFeeCategories());
            foreach (['dalam', 'luar'] as $type) {
                foreach ($categories as $category) {
                    BenchFeeRate::updateOrCreate(
                        ['level' => $name, 'type' => $type, 'category' => $category],
                        ['rate' => 0]
                    );
                }
            }
        });

        return back()->with('success', 'Jenjang "'.$validated['label'].'" berhasil ditambahkan.');
    }

    public function update(Request $request, BenchFeeLevel $level)
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $level->update($validated);

        return back()->with('success', 'Jenjang berhasil diperbarui.');
    }

    public function destroy(BenchFeeLevel $level)
    {
        DB::transaction(function () use ($level) {
            // Hapus semua rate terkait.
            BenchFeeRate::where('level', $level->name)->delete();
            $level->delete();
        });

        return back()->with('success', 'Jenjang berhasil dihapus.');
    }
}
