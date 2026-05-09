<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\OrderItemStatusLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SellerOrderController extends Controller
{
    public function index(): View
    {
        $seller = $this->requireRole(['pengguna']);
        $items = OrderItem::with('order.buyer', 'product')
            ->where('seller_id', $seller->id)
            ->orderByDesc('created_at')
            ->get();

        return view('seller.orders.index', [
            'seller' => $seller,
            'items' => $items,
        ]);
    }

    public function process(OrderItem $item): RedirectResponse
    {
        $seller = $this->requireRole(['pengguna']);

        if ($item->seller_id !== $seller->id) {
            abort(403);
        }

        if ($item->status !== 'menunggu_penjual') {
            return back()->withErrors(['status' => 'Status tidak valid untuk diproses.']);
        }

        $this->updateStatus($item, 'menunggu_penjual', 'diproses_penjual', $seller->id, 'Penjual memproses pesanan.');

        return back()->with('success', 'Status diperbarui menjadi diproses penjual.');
    }

    public function callCourier(OrderItem $item): RedirectResponse
    {
        $seller = $this->requireRole(['pengguna']);

        if ($item->seller_id !== $seller->id) {
            abort(403);
        }

        if ($item->status !== 'diproses_penjual') {
            return back()->withErrors(['status' => 'Status tidak valid untuk memanggil kurir.']);
        }

        $this->updateStatus($item, 'diproses_penjual', 'menunggu_kurir', $seller->id, 'Penjual memanggil kurir.');

        return back()->with('success', 'Status diperbarui menjadi menunggu kurir.');
    }

    public function approveReturn(OrderItem $item): RedirectResponse
    {
        $seller = $this->requireRole(['pengguna']);

        if ($item->seller_id !== $seller->id) {
            abort(403);
        }

        if ($item->status !== 'dikomplain') {
            return back()->withErrors(['status' => 'Status tidak valid untuk retur.']);
        }

        $this->updateStatus($item, 'dikomplain', 'dikirim_balik', $seller->id, 'Komplain disetujui, proses retur.');

        return back()->with('success', 'Status diperbarui menjadi dikirim balik.');
    }

    public function rejectComplaint(OrderItem $item): RedirectResponse
    {
        $seller = $this->requireRole(['pengguna']);

        if ($item->seller_id !== $seller->id) {
            abort(403);
        }

        if ($item->status !== 'dikomplain') {
            return back()->withErrors(['status' => 'Status tidak valid untuk menolak komplain.']);
        }

        $this->updateStatus($item, 'dikomplain', 'sampai_di_tujuan', $seller->id, 'Komplain ditolak oleh penjual.');

        return back()->with('success', 'Komplain ditolak dan status kembali ke sampai di tujuan.');
    }

    public function markFailed(OrderItem $item): RedirectResponse
    {
        $seller = $this->requireRole(['pengguna']);

        if ($item->seller_id !== $seller->id) {
            abort(403);
        }

        if ($item->status !== 'menunggu_penjual' || !$item->alasan_komplain) {
            return back()->withErrors(['status' => 'Status tidak valid untuk transaksi gagal.']);
        }

        DB::transaction(function () use ($item, $seller) {
            $this->updateStatus($item, 'menunggu_penjual', 'transaksi_gagal', $seller->id, 'Retur diterima penjual.');

            $product = $item->product;
            $product->stok += $item->jumlah;
            $product->status = $product->stok > 0 ? 'stok_tersedia' : 'stok_kosong';
            $product->save();

            $order = $item->order;
            $buyerWallet = $order->buyer->wallet;
            if ($buyerWallet) {
                $buyerWallet->saldo += $item->subtotal;
                $buyerWallet->save();
            }

            $order->escrow_status = 'dikembalikan';
            $order->refunded_at = Carbon::now();
            $order->save();
        });

        return back()->with('success', 'Transaksi gagal dan dana dikembalikan ke pembeli.');
    }

    private function updateStatus(OrderItem $item, string $from, string $to, int $accountId, string $note): void
    {
        $item->status = $to;
        if ($to === 'transaksi_gagal') {
            $item->failed_at = Carbon::now();
        }
        $item->save();

        OrderItemStatusLog::create([
            'order_item_id' => $item->id,
            'status_lama' => $from,
            'status_baru' => $to,
            'diubah_oleh_account_id' => $accountId,
            'catatan' => $note,
            'created_at' => Carbon::now(),
        ]);
    }
}
