@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold">Detail Pesanan</h1>
    <p class="text-sm text-slate-400">Nomor pesanan: {{ $order->nomor_pesanan }}</p>
</div>

<div class="space-y-4">
    @foreach ($order->items as $item)
        <div class="glass-card rounded-2xl p-4 shadow-sm">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    @if($item->gambar_produk_snapshot)
                        <img src="{{ $item->gambar_produk_snapshot }}" alt="{{ $item->nama_produk_snapshot }}" class="h-16 w-16 rounded-lg object-cover" />
                    @else
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-slate-800 text-xs text-slate-500">No Img</div>
                    @endif
                    <div>
                        <p class="text-lg font-semibold">{{ $item->nama_produk_snapshot }}</p>
                        <p class="text-sm text-slate-400">Penjual: {{ $item->seller->nama_lengkap }}</p>
                        <p class="text-sm text-slate-400">Jumlah: {{ $item->jumlah }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-slate-400">Status</p>
                    <p class="text-lg font-semibold">{{ $item->status }}</p>
                </div>
            </div>
            @if ($item->status === 'sampai_di_tujuan')
                <div class="mt-4 flex flex-col gap-3 md:flex-row md:items-center">
                    <form method="POST" action="{{ route('orders.items.confirm', $item) }}">
                        @csrf
                        <button class="glass-button glass-button-emerald rounded-lg px-3 py-2 text-sm font-semibold" type="submit">Konfirmasi Diterima</button>
                    </form>
                    <form method="POST" action="{{ route('orders.items.complain', $item) }}" class="flex flex-1 items-center gap-2">
                        @csrf
                        <input name="alasan_komplain" placeholder="Alasan komplain" class="flex-1 rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-100" required />
                        <button class="glass-button rounded-lg border border-rose-500/40 px-3 py-2 text-sm font-semibold text-rose-200" type="submit">Komplain</button>
                    </form>
                </div>
            @elseif ($item->status === 'dikomplain')
                <p class="mt-3 text-sm text-rose-300">Komplain: {{ $item->alasan_komplain }}</p>
            @endif
        </div>
    @endforeach
</div>
@endsection
