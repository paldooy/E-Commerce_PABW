<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): \Illuminate\Http\RedirectResponse|View
    {
        $account = $this->currentAccount();
        if (!$account) {
            return redirect()->route('login');
        }

        if ($account->role === 'admin') {
            return redirect()->route('admin.users.index');
        }
        
        if ($account->role === 'kurir') {
            return redirect()->route('courier.index');
        }

        // Dashboard untuk Pengguna (Beranda)
        $buying_active = Order::where('buyer_id', $account->id)
            ->where('escrow_status', '!=', 'dilepas')
            ->where('escrow_status', '!=', 'dikembalikan')
            ->count();

        $selling_active = OrderItem::where('seller_id', $account->id)
            ->whereNotIn('status', ['diterima_pembeli', 'transaksi_gagal'])
            ->count();

        $latest_products = Product::with('seller')
            ->where('status', 'stok_tersedia')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('dashboard.index', [
            'account' => $account,
            'buying_active' => $buying_active,
            'selling_active' => $selling_active,
            'latest_products' => $latest_products,
        ]);
    }
}
