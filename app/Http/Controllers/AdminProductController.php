<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminProductController extends Controller
{
    public function index(): View
    {
        $admin = $this->requireRole(['admin']);
        $products = Product::with('seller')->orderByDesc('created_at')->get();

        return view('admin.products.index', [
            'admin' => $admin,
            'products' => $products,
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->requireRole(['admin']);

        $data = $request->validate([
            'status' => ['required', 'in:stok_tersedia,stok_kosong'],
        ]);

        $product->status = $data['status'];
        $product->save();

        return back()->with('success', 'Status produk diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->requireRole(['admin']);

        $product->delete();

        return back()->with('success', 'Produk dihapus.');
    }
}
