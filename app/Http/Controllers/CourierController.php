<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\OrderItemStatusLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CourierController extends Controller
{
    public function index(Request $request): View
    {
        $courier = $this->requireRole(['kurir']);
        $tab = $request->query('tab', 'tugas');

        if ($tab === 'tugas') {
            // Menunggu kurir mengambil barang (dari penjual untuk pembeli, atau ditarik dari pembeli untuk retur)
            $items = OrderItem::with('order.buyer', 'product', 'seller')
                ->whereIn('status', ['menunggu_kurir', 'dikirim_balik'])
                ->orderByDesc('updated_at')
                ->get();
        } elseif ($tab === 'berjalan') {
            // Sedang diantarkan
            $items = OrderItem::with('order.buyer', 'product', 'seller')
                ->where('status', 'sedang_dikirim')
                ->orderByDesc('updated_at')
                ->get();
        } else {
            // Riwayat pengantaran: semua yang telah berhasil diubah status log-nya oleh kurir ini menjadi titik akhir pengantaran
            $items = OrderItem::with(['order.buyer', 'product', 'seller', 'statusLogs' => function ($query) use ($courier) {
                $query->where('diubah_oleh_account_id', $courier->id)
                      ->whereIn('status_baru', ['sampai_di_tujuan', 'menunggu_penjual'])
                      ->orderByDesc('created_at');
            }])
            ->whereHas('statusLogs', function ($query) use ($courier) {
                $query->where('diubah_oleh_account_id', $courier->id)
                      ->whereIn('status_baru', ['sampai_di_tujuan', 'menunggu_penjual']);
            })
            ->orderByDesc('updated_at')
            ->get();
        }

        return view('courier.index', [
            'courier' => $courier,
            'items' => $items,
            'tab' => $tab,
        ]);
    }

    public function pickup(OrderItem $item): RedirectResponse
    {
        $courier = $this->requireRole(['kurir']);

        if ($item->status !== 'menunggu_kurir') {
            return back()->withErrors(['status' => 'Status tidak valid untuk pengambilan.']);
        }

        $this->updateStatus($item, 'menunggu_kurir', 'sedang_dikirim', $courier->id, 'Kurir mengambil barang.');

        return back()->with('success', 'Status diperbarui menjadi sedang dikirim.');
    }

    public function returnToBuyer(OrderItem $item): RedirectResponse
    {
        $courier = $this->requireRole(['kurir']);

        if ($item->status !== 'menunggu_kurir') {
            return back()->withErrors(['status' => 'Status tidak valid untuk dikirim balik.']);
        }

        $this->updateStatus($item, 'menunggu_kurir', 'dikirim_balik', $courier->id, 'Kurir mengembalikan barang ke pembeli.');

        return back()->with('success', 'Status diperbarui menjadi dikirim balik.');
    }

    public function delivered(OrderItem $item): RedirectResponse
    {
        $courier = $this->requireRole(['kurir']);

        if ($item->status !== 'sedang_dikirim') {
            return back()->withErrors(['status' => 'Status tidak valid untuk sampai di tujuan.']);
        }

        $this->updateStatus($item, 'sedang_dikirim', 'sampai_di_tujuan', $courier->id, 'Kurir mengkonfirmasi barang sampai.');

        return back()->with('success', 'Status diperbarui menjadi sampai di tujuan.');
    }

    public function returnToSeller(OrderItem $item): RedirectResponse
    {
        $courier = $this->requireRole(['kurir']);

        if ($item->status !== 'dikirim_balik') {
            return back()->withErrors(['status' => 'Status tidak valid untuk kembali ke penjual.']);
        }

        $this->updateStatus($item, 'dikirim_balik', 'menunggu_penjual', $courier->id, 'Kurir mengirim barang kembali ke penjual.');

        return back()->with('success', 'Status diperbarui menjadi menunggu penjual.');
    }

    private function updateStatus(OrderItem $item, string $from, string $to, int $accountId, string $note): void
    {
        $item->status = $to;
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
