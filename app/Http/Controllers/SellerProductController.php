<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SellerProductController extends Controller
{
    public function index(): View
    {
        $seller = $this->requireRole(['pengguna']);
        $products = Product::where('seller_id', $seller->id)
            ->orderByDesc('created_at')
            ->get();

        return view('seller.products.index', [
            'seller' => $seller,
            'products' => $products,
        ]);
    }

    public function create(): View
    {
        $seller = $this->requireRole(['pengguna']);

        return view('seller.products.create', [
            'seller' => $seller,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $seller = $this->requireRole(['pengguna']);

        $data = $request->validate([
            'nama_produk' => ['required', 'string', 'max:150'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'deskripsi' => ['required', 'string'],
            'foto_produk' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'harga' => ['required', 'numeric', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
        ]);

        $url_gambar = null;
        if ($request->hasFile('foto_produk')) {
            $path = $request->file('foto_produk')->store('products', 'public');
            $url_gambar = '/storage/' . $path;
        }

        Product::create([
            'seller_id' => $seller->id,
            'nama_produk' => $data['nama_produk'],
            'kategori' => $data['kategori'] ?? 'Lainnya',
            'deskripsi' => $data['deskripsi'],
            'url_gambar' => $url_gambar,
            'harga' => $data['harga'],
            'stok' => $data['stok'],
            'status' => $data['stok'] > 0 ? 'stok_tersedia' : 'stok_kosong',
        ]);

        return redirect()->route('seller.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        $seller = $this->requireRole(['pengguna']);

        if ($product->seller_id !== $seller->id) {
            abort(403);
        }

        return view('seller.products.edit', [
            'seller' => $seller,
            'product' => $product,
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $seller = $this->requireRole(['pengguna']);

        if ($product->seller_id !== $seller->id) {
            abort(403);
        }

        $data = $request->validate([
            'nama_produk' => ['required', 'string', 'max:150'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'deskripsi' => ['required', 'string'],
            'foto_produk' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'harga' => ['required', 'numeric', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
        ]);

        $url_gambar = $product->url_gambar;
        if ($request->hasFile('foto_produk')) {
            // Optional: delete old image here if exists
            $path = $request->file('foto_produk')->store('products', 'public');
            $url_gambar = '/storage/' . $path;
        }

        $product->update([
            'nama_produk' => $data['nama_produk'],
            'kategori' => $data['kategori'] ?? 'Lainnya',
            'deskripsi' => $data['deskripsi'],
            'url_gambar' => $url_gambar,
            'harga' => $data['harga'],
            'stok' => $data['stok'],
            'status' => $data['stok'] > 0 ? 'stok_tersedia' : 'stok_kosong',
        ]);

        return redirect()->route('seller.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $seller = $this->requireRole(['pengguna']);

        if ($product->seller_id !== $seller->id) {
            abort(403);
        }

        $product->delete();

        return redirect()->route('seller.products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
