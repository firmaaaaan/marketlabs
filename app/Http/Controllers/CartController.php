<?php

namespace App\Http\Controllers;

use App\Models\TestParameter;
use App\Models\Tool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Ambil isi keranjang dari session.
     */
    protected function cart(): array
    {
        return session('cart', []);
    }

    /**
     * Simpan keranjang ke session.
     */
    protected function saveCart(array $cart): void
    {
        session(['cart' => $cart]);
    }

    public function index()
    {
        return view('cart.index', $this->getCartData());
    }

    /**
     * Kembalikan data keranjang dalam format JSON.
     */
    public function json(): JsonResponse
    {
        return response()->json($this->getCartData());
    }

    /**
     * Kumpulkan semua data keranjang (alat + pengujian).
     */
    protected function getCartData(): array
    {
        // Keranjang alat (peminjaman)
        $items = [];
        $totalItems = 0;
        $totalCost = 0;

        foreach ($this->cart() as $toolId => $quantity) {
            $tool = Tool::find($toolId);

            if (! $tool || ! $tool->is_active) {
                continue;
            }

            $subtotal = $tool->price_per_day * $quantity;
            $totalCost += $subtotal;

            $items[] = [
                'tool' => $tool,
                'quantity' => $quantity,
                'max' => $tool->available_stock,
                'subtotal' => $subtotal,
            ];
            $totalItems += $quantity;
        }

        // Keranjang layanan pengujian sampel
        $testItems = [];
        $testTotalCost = 0;

        foreach (session('test_cart', []) as $parameterId => $_) {
            $parameter = TestParameter::with('unit')->find($parameterId);

            if (! $parameter || ! $parameter->is_active) {
                continue;
            }

            $testTotalCost += $parameter->rate;
            $testItems[] = [
                'parameter' => $parameter,
                'subtotal' => $parameter->rate,
            ];
        }

        $grandTotal = $totalCost + $testTotalCost;

        return compact('items', 'totalItems', 'totalCost', 'testItems', 'testTotalCost', 'grandTotal');
    }

    public function add(Request $request, Tool $tool)
    {
        abort_unless($tool->is_active, 404);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', "max:{$tool->available_stock}"],
        ]);

        $cart = $this->cart();
        $current = $cart[$tool->id] ?? 0;
        $cart[$tool->id] = $current + $validated['quantity'];

        if ($cart[$tool->id] > $tool->available_stock) {
            $cart[$tool->id] = $tool->available_stock;
        }

        $this->saveCart($cart);

        if ($request->expectsJson()) {
            $data = $this->getCartData();
            $data['success'] = "{$tool->name} ditambahkan ke keranjang peminjaman.";

            return response()->json($data);
        }

        return back()
            ->with('success', "{$tool->name} ditambahkan ke keranjang peminjaman.");
    }

    public function update(Request $request, Tool $tool)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', "max:{$tool->available_stock}"],
        ]);

        $cart = $this->cart();

        if ($validated['quantity'] === 0) {
            unset($cart[$tool->id]);
        } else {
            $cart[$tool->id] = $validated['quantity'];
        }

        $this->saveCart($cart);

        return redirect()->route('cart.index')
            ->with('success', 'Keranjang peminjaman diperbarui.');
    }

    public function remove(Tool $tool)
    {
        $cart = $this->cart();
        unset($cart[$tool->id]);
        $this->saveCart($cart);

        return redirect()->route('cart.index')
            ->with('success', "{$tool->name} dihapus dari keranjang.");
    }

    public function clear()
    {
        session()->forget('cart');

        return redirect()->route('cart.index')
            ->with('success', 'Keranjang peminjaman dikosongkan.');
    }
}
