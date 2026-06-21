<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $account = $this->currentAccount();
        $query = Product::with('seller')->where('status', 'stok_tersedia');
        
        if ($request->has('kategori') && $request->kategori !== '') {
            $query->where('kategori', $request->kategori);
        }
        
        $products = $query->orderByDesc('created_at')->get();
        $kategoriList = Product::select('kategori')->distinct()->pluck('kategori');

        return view('catalog.index', [
            'account' => $account,
            'products' => $products,
            'currentKategori' => $request->kategori,
            'kategoriList' => $kategoriList,
        ]);
    }

    public function show(Product $product): View
    {
        $account = $this->currentAccount();
        
        return view('catalog.show', [
            'account' => $account,
            'product' => $product,
        ]);
    }
}
