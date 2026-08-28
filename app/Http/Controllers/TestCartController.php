<?php

namespace App\Http\Controllers;

use App\Models\TestParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestCartController extends Controller
{
    /**
     * Ambil isi keranjang pengujian dari session (array parameter id => 1).
     * Keranjang hanya berisi pemilihan layanan; jumlah sampel diisi saat checkout.
     */
    protected function cart(): array
    {
        return session('test_cart', []);
    }

    protected function saveCart(array $cart): void
    {
        session(['test_cart' => $cart]);
    }

    /**
     * Keranjang pengujian kini ditampilkan bersama keranjang peminjaman di satu halaman.
     */
    public function index()
    {
        return redirect()->route('cart.index');
    }

    /**
     * Kembalikan isi keranjang pengujian dalam format JSON.
     */
    public function json(): JsonResponse
    {
        $cart = $this->cart();
        $items = [];
        $totalCost = 0;

        foreach ($cart as $parameterId => $_) {
            $parameter = \App\Models\TestParameter::with('unit')->find($parameterId);

            if (! $parameter || ! $parameter->is_active) {
                continue;
            }

            $totalCost += $parameter->rate;
            $items[] = [
                'parameter' => $parameter,
                'subtotal' => $parameter->rate,
            ];
        }

        return response()->json([
            'items' => $items,
            'total_cost' => $totalCost,
            'count' => count($items),
        ]);
    }

    public function add(Request $request, TestParameter $parameter)
    {
        abort_unless($parameter->is_active, 404);

        $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $cart = $this->cart();
        $cart[$parameter->id] = 1;
        $this->saveCart($cart);

        if ($request->expectsJson()) {
            $cartData = session('test_cart', []);
            $testItems = [];
            $testTotalCost = 0;

            foreach ($cartData as $parameterId => $_) {
                $param = \App\Models\TestParameter::with('unit')->find($parameterId);

                if (! $param || ! $param->is_active) {
                    continue;
                }

                $testTotalCost += $param->rate;
                $testItems[] = [
                    'parameter' => $param,
                    'subtotal' => $param->rate,
                ];
            }

            return response()->json([
                'test_items' => $testItems,
                'test_total_cost' => $testTotalCost,
                'test_count' => count($testItems),
                'success' => "{$parameter->name} ditambahkan ke keranjang pengujian.",
            ]);
        }

        return back()
            ->with('success', "{$parameter->name} ditambahkan ke keranjang pengujian.");
    }

    public function remove(TestParameter $parameter)
    {
        $cart = $this->cart();
        unset($cart[$parameter->id]);
        $this->saveCart($cart);

        return redirect()->route('cart.index')
            ->with('success', "{$parameter->name} dihapus dari keranjang pengujian.");
    }

    public function clear()
    {
        session()->forget('test_cart');

        return redirect()->route('cart.index')
            ->with('success', 'Keranjang pengujian dikosongkan.');
    }
}
