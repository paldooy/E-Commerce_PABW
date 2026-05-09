@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold">Pesanan Masuk</h1>
    <p class="text-sm text-slate-400">Kelola proses pesanan dari pembeli.</p>
</div>

<div class="space-y-4">
    @forelse ($items as $item)
        <div class="glass-card rounded-2xl p-4 shadow-sm">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-lg font-semibold">{{ $item->nama_produk_snapshot }}</p>
                    <p class="text-sm text-slate-400">Pembeli: {{ $item->order->buyer->nama_lengkap }}</p>
                    <p class="text-xs text-slate-500">Jumlah: {{ $item->jumlah }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-slate-400">Status</p>
                    <p class="text-lg font-semibold">{{ $item->status }}</p>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
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
        <div class="rounded-xl border border-dashed border-slate-800 p-6 text-center text-slate-400">Belum ada pesanan.</div>
    @endforelse
</div>
@endsection
