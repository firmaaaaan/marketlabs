<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BenchFeeRate;
use App\Models\ResearchProposal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminBenchFeeController extends Controller
{
    public function index()
    {
        $rates = ResearchProposal::benchFeeRates();

        return view('admin.bench-fee.index', compact('rates'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'rates' => ['required', 'array'],
            'rates.*.level' => ['required', Rule::in(['S1', 'S2/S3'])],
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
}
