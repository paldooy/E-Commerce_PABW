@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold">Tugas Pengiriman</h1>
    <p class="text-sm text-slate-400">Pantau dan kelola semua proses pengiriman Anda di sini.</p>
</div>

<!-- Tabs -->
<div class="mb-6 flex space-x-2 border-b border-slate-800 pb-2">
    <a href="{{ route('courier.index', ['tab' => 'tugas']) }}" class="px-4 py-2 text-sm font-medium transition-colors {{ $tab === 'tugas' ? 'text-emerald-400 border-b-2 border-emerald-400' : 'text-slate-400 hover:text-slate-300' }}">
        Tugas Pengiriman
    </a>
    <a href="{{ route('courier.index', ['tab' => 'berjalan']) }}" class="px-4 py-2 text-sm font-medium transition-colors {{ $tab === 'berjalan' ? 'text-emerald-400 border-b-2 border-emerald-400' : 'text-slate-400 hover:text-slate-300' }}">
        Pengiriman Sedang Berjalan
    </a>
    <a href="{{ route('courier.index', ['tab' => 'riwayat']) }}" class="px-4 py-2 text-sm font-medium transition-colors {{ $tab === 'riwayat' ? 'text-emerald-400 border-b-2 border-emerald-400' : 'text-slate-400 hover:text-slate-300' }}">
        Riwayat Pengantaran
    </a>
</div>

@if($tab === 'tugas')
    <div class="space-y-4">
        @forelse ($items as $item)
            <div class="glass-card rounded-2xl p-4 shadow-sm">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <p class="text-lg font-semibold">{{ $item->nama_produk_snapshot }}</p>
                            @if ($item->status === 'dikirim_balik')
                                <span class="rounded border border-rose-500/40 bg-rose-500/10 px-2 py-0.5 text-xs text-rose-300">Pengembalian Barang Retur</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-slate-400">Penjual: {{ $item->seller->nama_lengkap }} | Pembeli: {{ $item->order->buyer->nama_lengkap }}</p>
                        <p class="text-xs text-slate-500">Jumlah: {{ $item->jumlah }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-slate-400">Status</p>
                        <p class="text-lg font-semibold capitalize">{{ str_replace('_', ' ', $item->status) }}</p>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    @if ($item->status === 'menunggu_kurir')
                        <form method="POST" action="{{ route('courier.items.pickup', $item) }}">
                            @csrf
                            <button class="glass-button glass-button-emerald rounded-lg px-3 py-2 text-sm font-semibold" type="submit">Ambil Barang</button>
                        </form>
                    @endif
                    @if ($item->status === 'dikirim_balik')
                        <form method="POST" action="{{ route('courier.items.returnSeller', $item) }}">
                            @csrf
                            <button class="glass-button rounded-lg border border-amber-500/40 px-3 py-2 text-sm text-amber-200" type="submit">Ambil Barang & Menuju ke Penjual</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-slate-800 p-6 text-center text-slate-400">Belum ada tugas baru yang masuk.</div>
        @endforelse
    </div>
@elseif($tab === 'berjalan')
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
                        <p class="text-lg font-semibold capitalize text-emerald-400">{{ str_replace('_', ' ', $item->status) }}</p>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    @if ($item->status === 'sedang_dikirim')
                        <form method="POST" action="{{ route('courier.items.delivered', $item) }}">
                            @csrf
                            <button class="glass-button glass-button-emerald rounded-lg px-3 py-2 text-sm font-semibold" type="submit">Sampai Tujuan</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-slate-800 p-6 text-center text-slate-400">Tidak ada pengiriman yang sedang berjalan.</div>
        @endforelse
    </div>
@else
    <div class="space-y-4">
        @forelse ($items as $item)
            @php
                $lastCourierLog = $item->statusLogs->first();
                $isRetur = $lastCourierLog && $lastCourierLog->status_baru === 'menunggu_penjual';
            @endphp
            <div class="glass-card rounded-2xl p-4 shadow-sm border border-slate-800/50">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <p class="text-lg font-semibold">{{ $item->nama_produk_snapshot }}</p>
                            @if ($isRetur)
                                <span class="rounded border border-rose-500/40 bg-rose-500/10 px-2 py-0.5 text-xs text-rose-300">Pengembalian Barang Retur</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-slate-400">Penjual: {{ $item->seller->nama_lengkap }} | Pembeli: {{ $item->order->buyer->nama_lengkap }}</p>
                        <p class="text-xs text-slate-500">Jumlah: {{ $item->jumlah }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-slate-400">Status Akhir Pengantaran</p>
                        <p class="text-base font-semibold text-emerald-400">
                            {{ $isRetur ? 'Diterima Penjual' : 'Diterima Pembeli' }}
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-slate-800 p-6 text-center text-slate-400">Belum ada riwayat pengantaran yang diselesaikan.</div>
        @endforelse
    </div>
@endif
@endsection
