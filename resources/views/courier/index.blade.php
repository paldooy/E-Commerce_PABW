@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold">Tugas Pengiriman</h1>
    <p class="text-sm text-slate-400">Daftar tugas dengan status menunggu kurir atau sedang dikirim.</p>
</div>

<div class="space-y-4">
    @forelse ($items as $item)
        <div class="glass-card rounded-2xl p-4 shadow-sm">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-lg font-semibold">{{ $item->nama_produk_snapshot }}</p>
                    <p class="text-sm text-slate-400">Penjual: {{ $item->seller->nama_lengkap }} | Pembeli: {{ $item->order->buyer->nama_lengkap }}</p>
                    <p class="text-xs text-slate-500">Jumlah: {{ $item->jumlah }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-slate-400">Status</p>
                    <p class="text-lg font-semibold">{{ $item->status }}</p>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                @if ($item->status === 'menunggu_kurir')
                    <form method="POST" action="{{ route('courier.items.pickup', $item) }}">
                        @csrf
                        <button class="glass-button glass-button-emerald rounded-lg px-3 py-2 text-sm font-semibold" type="submit">Ambil Barang</button>
                    </form>
                    <form method="POST" action="{{ route('courier.items.returnBuyer', $item) }}">
                        @csrf
                        <button class="glass-button rounded-lg border border-rose-500/40 px-3 py-2 text-sm text-rose-200" type="submit">Kirim Balik</button>
                    </form>
                @endif
                @if ($item->status === 'sedang_dikirim')
                    <form method="POST" action="{{ route('courier.items.delivered', $item) }}">
                        @csrf
                        <button class="glass-button glass-button-emerald rounded-lg px-3 py-2 text-sm font-semibold" type="submit">Sampai Tujuan</button>
                    </form>
                @endif
                @if ($item->status === 'dikirim_balik')
                    <form method="POST" action="{{ route('courier.items.returnSeller', $item) }}">
                        @csrf
                        <button class="glass-button rounded-lg px-3 py-2 text-sm" type="submit">Kembali ke Penjual</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-dashed border-slate-800 p-6 text-center text-slate-400">Belum ada tugas.</div>
    @endforelse
</div>
@endsection
