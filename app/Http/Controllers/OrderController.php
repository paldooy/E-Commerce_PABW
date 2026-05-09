<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemStatusLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = $this->requireRole(['pengguna']);
        $tab = $request->query('tab', 'pembelian');

        // Pembelian: Pesanan pengguna (pembeli) yang belum selesai sepenuhnya
        $buying_orders = Order::with('items.product')
            ->where('buyer_id', $user->id)
            ->where('escrow_status', '!=', 'dilepas')
            ->where('escrow_status', '!=', 'dikembalikan')
            ->orderByDesc('created_at')
            ->get();

        // Penjualan: Item pesanan di toko pengguna yang belum selesai
        $selling_items = OrderItem::with('order.buyer', 'product')
            ->where('seller_id', $user->id)
            ->whereNotIn('status', ['diterima_pembeli', 'transaksi_gagal'])
            ->orderByDesc('created_at')
            ->get();

        // Riwayat: Semua order sebagai pembeli dan item sebagai penjual yang sudah selesai
        $history_buying = Order::with('items.product')
            ->where('buyer_id', $user->id)
            ->whereIn('escrow_status', ['dilepas', 'dikembalikan'])
            ->orderByDesc('created_at')
            ->get();
            
        $history_selling = OrderItem::with('order.buyer', 'product')
            ->where('seller_id', $user->id)
            ->whereIn('status', ['diterima_pembeli', 'transaksi_gagal'])
            ->orderByDesc('created_at')
            ->get();

        return view('orders.index', [
            'buyer' => $user,
            'tab' => $tab,
            'buying_orders' => $buying_orders,
            'selling_items' => $selling_items,
            'history_buying' => $history_buying,
            'history_selling' => $history_selling,
        ]);
    }

    public function show(Order $order): View
    {
        $buyer = $this->requireRole(['pengguna']);

        if ($order->buyer_id !== $buyer->id) {
            abort(403);
        }

        $order->load('items.product', 'items.seller');

        return view('orders.show', [
            'buyer' => $buyer,
            'order' => $order,
        ]);
    }

    public function confirmReceived(OrderItem $item): RedirectResponse
    {
        $buyer = $this->requireRole(['pengguna']);

        if ($item->order->buyer_id !== $buyer->id) {
            abort(403);
        }

        if ($item->status !== 'sampai_di_tujuan') {
            return back()->withErrors(['status' => 'Status barang belum sampai di tujuan.']);
        }

        DB::transaction(function () use ($item, $buyer) {
            $item->status = 'diterima_pembeli';
            $item->save();

            OrderItemStatusLog::create([
                'order_item_id' => $item->id,
                'status_lama' => 'sampai_di_tujuan',
                'status_baru' => 'diterima_pembeli',
                'diubah_oleh_account_id' => $buyer->id,
                'catatan' => 'Pembeli mengkonfirmasi barang diterima.',
                'created_at' => Carbon::now(),
            ]);

            $sellerWallet = $item->seller->wallet;
            if ($sellerWallet) {
                $sellerWallet->saldo += $item->subtotal;
                $sellerWallet->save();
            }

            $order = $item->order;
            $allReceived = $order->items()->where('status', '!=', 'diterima_pembeli')->count() === 0;

            if ($allReceived && $order->escrow_status !== 'dilepas') {
                $order->escrow_status = 'dilepas';
                $order->released_at = Carbon::now();
                $order->save();
            }
        });

        return back()->with('success', 'Status diperbarui menjadi diterima pembeli.');
    }

    public function complain(Request $request, OrderItem $item): RedirectResponse
    {
        $buyer = $this->requireRole(['pengguna']);

        if ($item->order->buyer_id !== $buyer->id) {
            abort(403);
        }

        if ($item->status !== 'sampai_di_tujuan') {
            return back()->withErrors(['status' => 'Barang belum sampai di tujuan.']);
        }

        $data = $request->validate([
            'alasan_komplain' => ['required', 'string', 'max:255'],
        ]);

        $item->status = 'dikomplain';
        $item->alasan_komplain = $data['alasan_komplain'];
        $item->save();

        OrderItemStatusLog::create([
            'order_item_id' => $item->id,
            'status_lama' => 'sampai_di_tujuan',
            'status_baru' => 'dikomplain',
            'diubah_oleh_account_id' => $buyer->id,
            'catatan' => 'Pembeli mengajukan komplain.',
            'created_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Komplain berhasil dikirim.');
    }
}
