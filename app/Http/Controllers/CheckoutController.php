<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemStatusLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(): View
    {
        $buyer = $this->requireRole(['pengguna']);
        $cart = Cart::firstOrCreate(['buyer_id' => $buyer->id]);
        $cart->load('items.product');

        $total = $cart->items->sum(function ($item) {
            return $item->product->harga * $item->jumlah;
        });

        return view('checkout.show', [
            'buyer' => $buyer,
            'cart' => $cart,
            'total' => $total,
        ]);
    }

    public function process(Request $request): RedirectResponse
    {
        $buyer = $this->requireRole(['pengguna']);
        $cart = Cart::firstOrCreate(['buyer_id' => $buyer->id]);
        $cart->load('items.product');

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.show')->withErrors(['cart' => 'Keranjang masih kosong.']);
        }

        $wallet = $buyer->wallet;

        if (!$wallet) {
            return redirect()->route('cart.show')->withErrors(['wallet' => 'Wallet tidak ditemukan.']);
        }

        $total = $cart->items->sum(function ($item) {
            return $item->product->harga * $item->jumlah;
        });

        foreach ($cart->items as $item) {
            if ($item->jumlah > $item->product->stok) {
                return redirect()->route('cart.show')->withErrors([
                    'stok' => 'Stok produk tidak mencukupi untuk ' . $item->product->nama_produk,
                ]);
            }
        }

        if ($wallet->saldo < $total) {
            return redirect()->route('cart.show')->withErrors(['saldo' => 'Saldo tidak mencukupi.']);
        }

        $order = DB::transaction(function () use ($buyer, $cart, $total, $wallet) {
            $order = Order::create([
                'nomor_pesanan' => 'ORD-' . strtoupper(Str::random(10)),
                'buyer_id' => $buyer->id,
                'total_harga' => $total,
                'escrow_status' => 'tertahan',
                'paid_at' => Carbon::now(),
            ]);

            foreach ($cart->items as $item) {
                $product = $item->product;
                $product->stok -= $item->jumlah;
                $product->status = $product->stok > 0 ? 'stok_tersedia' : 'stok_kosong';
                $product->save();

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'seller_id' => $product->seller_id,
                    'nama_produk_snapshot' => $product->nama_produk,
                    'deskripsi_produk_snapshot' => $product->deskripsi,
                    'gambar_produk_snapshot' => $product->url_gambar,
                    'harga_satuan_snapshot' => $product->harga,
                    'jumlah' => $item->jumlah,
                    'subtotal' => $product->harga * $item->jumlah,
                    'status' => 'menunggu_penjual',
                ]);

                OrderItemStatusLog::create([
                    'order_item_id' => $orderItem->id,
                    'status_lama' => 'menunggu_penjual',
                    'status_baru' => 'menunggu_penjual',
                    'diubah_oleh_account_id' => $buyer->id,
                    'catatan' => 'Pesanan dibayar oleh pembeli.',
                    'created_at' => Carbon::now(),
                ]);
            }

            $wallet->saldo -= $total;
            $wallet->save();

            $cart->items()->delete();

            return $order;
        });

        return redirect()->route('orders.show', $order)->with('success', 'Pembayaran berhasil.');
    }
}
