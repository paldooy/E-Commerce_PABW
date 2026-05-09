<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function show(): View
    {
        $buyer = $this->requireRole(['pengguna']);
        $cart = Cart::firstOrCreate(['buyer_id' => $buyer->id]);
        $cart->load('items.product');

        return view('cart.show', [
            'buyer' => $buyer,
            'cart' => $cart,
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $buyer = $this->requireRole(['pengguna']);

        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        if ($data['jumlah'] > $product->stok) {
            return back()->withErrors(['jumlah' => 'Jumlah melebihi stok tersedia.']);
        }

        $cart = Cart::firstOrCreate(['buyer_id' => $buyer->id]);
        $item = CartItem::firstOrNew([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ]);

        $newQty = $item->exists ? $item->jumlah + $data['jumlah'] : $data['jumlah'];

        if ($newQty > $product->stok) {
            return back()->withErrors(['jumlah' => 'Jumlah melebihi stok tersedia.']);
        }

        $item->jumlah = $newQty;
        $item->save();

        return back()->with('success', 'Barang ditambahkan ke keranjang.');
    }

    public function update(Request $request, CartItem $item): RedirectResponse
    {
        $buyer = $this->requireRole(['pengguna']);

        if ($item->cart->buyer_id !== $buyer->id) {
            abort(403);
        }

        $data = $request->validate([
            'jumlah' => ['required', 'integer', 'min:0'],
        ]);

        $product = $item->product;

        if ($data['jumlah'] === 0) {
            $item->delete();

            return redirect()->route('cart.show')->with('success', 'Barang dihapus dari keranjang.');
        }

        if ($data['jumlah'] > $product->stok) {
            return back()->withErrors(['jumlah' => 'Jumlah melebihi stok tersedia.']);
        }

        $item->update(['jumlah' => $data['jumlah']]);

        return redirect()->route('cart.show')->with('success', 'Jumlah barang diperbarui.');
    }

    public function remove(CartItem $item): RedirectResponse
    {
        $buyer = $this->requireRole(['pengguna']);

        if ($item->cart->buyer_id !== $buyer->id) {
            abort(403);
        }

        $item->delete();

        return redirect()->route('cart.show')->with('success', 'Barang dihapus dari keranjang.');
    }
}
