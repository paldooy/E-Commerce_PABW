@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold">Pesanan Saya</h1>
    <p class="text-sm text-slate-400">Pantau dan kelola semua proses pesanan Anda di sini.</p>
</div>

<!-- Tabs -->
<div class="mb-6 flex space-x-2 border-b border-slate-800 pb-2">
    <a href="{{ route('orders.index', ['tab' => 'pembelian']) }}" class="px-4 py-2 text-sm font-medium transition-colors {{ $tab === 'pembelian' ? 'text-emerald-400 border-b-2 border-emerald-400' : 'text-slate-400 hover:text-slate-300' }}">
        Status Pembelian
    </a>
    <a href="{{ route('orders.index', ['tab' => 'penjualan']) }}" class="px-4 py-2 text-sm font-medium transition-colors {{ $tab === 'penjualan' ? 'text-emerald-400 border-b-2 border-emerald-400' : 'text-slate-400 hover:text-slate-300' }}">
        Pesanan Masuk (Penjualan)
    </a>
    <a href="{{ route('orders.index', ['tab' => 'riwayat']) }}" class="px-4 py-2 text-sm font-medium transition-colors {{ $tab === 'riwayat' ? 'text-emerald-400 border-b-2 border-emerald-400' : 'text-slate-400 hover:text-slate-300' }}">
        Riwayat Selesai
    </a>
</div>

<!-- Tab Content -->
@if($tab === 'pembelian')
    <div class="space-y-4">
        @forelse ($buying_orders as $order)
            <div class="glass-card rounded-2xl p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-400">Nomor Pesanan</p>
                        <p class="text-lg font-semibold">{{ $order->nomor_pesanan }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-slate-400">Total Belanja</p>
                        <p class="text-lg font-semibold text-emerald-400">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between border-t border-slate-800 pt-3">
                    <p class="text-sm text-slate-400">Escrow: <span class="capitalize">{{ str_replace('_', ' ', $order->escrow_status) }}</span></p>
                    <a class="glass-button rounded-lg px-4 py-1 text-sm font-medium" href="{{ route('orders.show', $order) }}">Lihat Detail Status Barang</a>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-slate-800 p-8 text-center text-slate-400">Tidak ada pesanan pembelian yang sedang berjalan.</div>
        @endforelse
    </div>

@elseif($tab === 'penjualan')
    <div class="space-y-4">
        @forelse ($selling_items as $item)
            <div class="glass-card rounded-2xl p-4 shadow-sm">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-lg font-semibold">{{ $item->nama_produk_snapshot }}</p>
                        <p class="text-sm text-slate-400">Pembeli: {{ $item->order->buyer->nama_lengkap }}</p>
                        <p class="text-xs text-slate-500">Jumlah: {{ $item->jumlah }} x Rp {{ number_format($item->harga_snapshot, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-slate-400">Status Saat Ini</p>
                        <p class="text-base font-semibold capitalize text-emerald-400">{{ str_replace('_', ' ', $item->status) }}</p>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2 border-t border-slate-800 pt-3">
                    @if ($item->status === 'menunggu_penjual')
                        <form method="POST" action="{{ route('seller.orders.process', $item) }}">
                            @csrf
                            <button class="glass-button glass-button-emerald rounded-lg px-3 py-2 text-sm font-semibold" type="submit">Proses Pesanan</button>
                        </form>
                    @endif
                    @if ($item->status === 'diproses_penjual')
                        <form method="POST" action="{{ route('seller.orders.callCourier', $item) }}">
                            @csrf
                            <button class="glass-button rounded-lg px-3 py-2 text-sm" type="submit">Panggil Kurir</button>
                        </form>
                    @endif
                    @if ($item->status === 'dikomplain')
                        <form method="POST" action="{{ route('seller.orders.approveReturn', $item) }}">
                            @csrf
                            <button class="glass-button rounded-lg border border-rose-500/40 px-3 py-2 text-sm text-rose-200" type="submit">Setujui Retur</button>
                        </form>
                        <form method="POST" action="{{ route('seller.orders.rejectComplaint', $item) }}">
                            @csrf
                            <button class="glass-button rounded-lg px-3 py-2 text-sm" type="submit">Tolak Komplain</button>
                        </form>
                    @endif
                    @if ($item->status === 'menunggu_penjual' && $item->alasan_komplain)
                        <form method="POST" action="{{ route('seller.orders.markFailed', $item) }}">
                            @csrf
                            <button class="glass-button rounded-lg border border-rose-500/40 px-3 py-2 text-sm text-rose-200" type="submit">Tandai Transaksi Gagal</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-slate-800 p-8 text-center text-slate-400">Belum ada pesanan masuk yang perlu diproses.</div>
        @endforelse
    </div>

@elseif($tab === 'riwayat')
    <div class="grid gap-6 md:grid-cols-2">
        <!-- Riwayat Sebagai Pembeli -->
        <div>
            <h2 class="mb-3 text-lg font-medium text-slate-300">Riwayat Pembelian</h2>
            <div class="space-y-3">
                @forelse ($history_buying as $order)
                    <div class="glass-card rounded-2xl p-4 shadow-sm border border-slate-800/50">
                        <p class="text-sm text-slate-400">{{ $order->nomor_pesanan }}</p>
                        <p class="text-base font-medium">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</p>
                        <p class="mt-2 text-xs capitalize {{ $order->escrow_status === 'dikembalikan' ? 'text-rose-400' : 'text-emerald-400' }}">{{ str_replace('_', ' ', $order->escrow_status) }}</p>
                        <div class="mt-2 text-right">
                            <a class="text-xs text-slate-300 hover:text-emerald-400 underline" href="{{ route('orders.show', $order) }}">Lihat Detail</a>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Tidak ada riwayat pembelian.</p>
                @endforelse
            </div>
        </div>

        <!-- Riwayat Sebagai Penjual -->
        <div>
            <h2 class="mb-3 text-lg font-medium text-slate-300">Riwayat Penjualan</h2>
            <div class="space-y-3">
                @forelse ($history_selling as $item)
                    <div class="glass-card rounded-2xl p-4 shadow-sm border border-slate-800/50">
                        <p class="text-sm text-slate-400">{{ $item->order->nomor_pesanan }}</p>
                        <p class="text-base font-medium">{{ $item->nama_produk_snapshot }} ({{ $item->jumlah }}x)</p>
                        <p class="mt-2 text-xs capitalize {{ $item->status === 'transaksi_gagal' ? 'text-rose-400' : 'text-emerald-400' }}">{{ str_replace('_', ' ', $item->status) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Tidak ada riwayat penjualan.</p>
                @endforelse
            </div>
        </div>
    </div>
@endif
@endsection
