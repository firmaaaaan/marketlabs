<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BenchFeeLevel;
use App\Models\BenchFeeRate;
use App\Models\ResearchProposal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminBenchFeeController extends Controller
{
    public function index()
    {
        $rates = ResearchProposal::benchFeeRates();
        $levels = BenchFeeLevel::orderBy('sort_order')->get();
        $rateModels = BenchFeeRate::all()->keyBy(fn ($r) => $r->level.'|'.$r->type.'|'.$r->category);

        return view('admin.bench-fee.index', compact('rates', 'levels', 'rateModels'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'rates' => ['required', 'array'],
            'rates.*.level' => ['required', Rule::in(\App\Models\BenchFeeLevel::names())],
            'rates.*.type' => ['required', Rule::in(['dalam', 'luar'])],
            'rates.*.category' => ['required', Rule::in(array_keys(ResearchProposal::benchFeeCategories()))],
            'rates.*.rate' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['rates'] as $rate) {
            BenchFeeRate::updateOrCreate(
                ['level' => $rate['level'], 'type' => $rate['type'], 'category' => $rate['category']],
                ['rate' => $rate['rate']],
            );
        }

        return redirect()->route('admin.bench-fee.index')
            ->with('success', 'Tarif bench fee berhasil diperbarui.');
    }

    public function destroy(BenchFeeRate $rate)
    {
        $rate->delete();

        return back()->with('success', 'Tarif berhasil dihapus.');
    }
}
