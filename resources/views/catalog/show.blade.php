@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-semibold">Detail Produk</h1>
        <p class="text-sm text-slate-400">Informasi lengkap tentang produk ini.</p>
    </div>
    <a href="{{ route('catalog.index') }}" class="glass-button rounded-lg px-4 py-2 text-sm">Kembali ke Katalog</a>
</div>

<div class="glass-card rounded-2xl p-6 shadow-sm">
    <div class="grid gap-8 md:grid-cols-2">
        <!-- Foto Produk -->
        <div class="overflow-hidden rounded-xl border border-white/10 bg-white/5">
            @if ($product->url_gambar)
                <img src="{{ $product->url_gambar }}" alt="{{ $product->nama_produk }}" class="h-full w-full object-cover" />
            @else
                <div class="flex aspect-square items-center justify-center text-slate-400">Tanpa foto</div>
            @endif
        </div>

        <!-- Info Produk -->
        <div class="flex flex-col justify-between">
            <div>
                @if($product->kategori)
                    <span class="mb-2 inline-block rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-400 border border-emerald-500/20">
                        {{ $product->kategori }}
                    </span>
                @endif
                <h2 class="mb-2 text-3xl font-bold">{{ $product->nama_produk }}</h2>
                
                <div class="mb-6 flex items-center gap-3">
                    <div class="flex items-center justify-center h-8 w-8 rounded-full bg-indigo-500/20 border border-indigo-500/50 text-indigo-400 font-bold">
                        {{ substr($product->seller->nama_lengkap, 0, 1) }}
                    </div>
                    <div>
                        <p class="text-sm text-slate-300">Dijual oleh</p>
                        <p class="text-sm font-semibold">{{ $product->seller->nama_lengkap }}</p>
                    </div>
                </div>

                <div class="mb-6">
                    <p class="mb-1 text-sm text-slate-400 uppercase tracking-wider">Deskripsi Produk</p>
                    <div class="prose prose-invert max-w-none text-slate-300">
                        {!! nl2br(e($product->deskripsi)) !!}
                    </div>
                </div>
            </div>

            <!-- Harga & Keranjang -->
            <div class="mt-6 rounded-xl border border-white/10 bg-black/20 p-5">
                <div class="mb-4 flex items-end justify-between">
                    <div>
                        <p class="text-sm text-slate-400">Harga</p>
                        <p class="text-3xl font-bold text-emerald-400">Rp {{ number_format($product->harga, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-slate-400">Sisa Stok</p>
                        <p class="text-lg font-semibold">{{ $product->stok }}</p>
                    </div>
                </div>

                @if($product->stok > 0 && $product->status === 'stok_tersedia')
                    <form method="POST" action="{{ route('cart.add') }}" class="flex items-center gap-3">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}" />
                        <div class="flex items-center gap-2">
                            <label for="jumlah" class="text-sm text-slate-300">Jumlah:</label>
                            <input type="number" id="jumlah" name="jumlah" min="1" max="{{ $product->stok }}" value="1" class="w-20 rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100 text-center focus:border-emerald-500/50 focus:outline-none focus:ring-1 focus:ring-emerald-500/50" />
                        </div>
                        <button type="submit" class="glass-button glass-button-emerald flex-1 rounded-lg py-2 font-semibold flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Tambah ke Keranjang
                        </button>
                    </form>
                @else
                    <button disabled class="w-full rounded-lg bg-slate-800 py-3 text-center font-semibold text-slate-500 cursor-not-allowed">
                        Stok Habis
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
