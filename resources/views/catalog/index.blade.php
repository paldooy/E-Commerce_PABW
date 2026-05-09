@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-semibold">Katalog Produk</h1>
        <p class="text-sm text-slate-400">Temukan barang-barang terbaik di katalog kami.</p>
    </div>
    
    <div class="flex items-center gap-2">
        <form method="GET" action="{{ route('catalog.index') }}" class="flex gap-2">
            <select name="kategori" onchange="this.form.submit()" class="glass-button rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500/50">
                <option value="" style="background:#0f172a">Semua Kategori</option>
                @foreach ($kategoriList as $kat)
                    @if($kat)
                        <option value="{{ $kat }}" style="background:#0f172a" {{ $currentKategori == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                    @endif
                @endforeach
            </select>
        </form>
        <a class="glass-button rounded-lg px-3 py-2 text-sm" href="{{ route('cart.show') }}">Lihat Keranjang</a>
    </div>
</div>

<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
    @forelse ($products as $product)
        <div class="glass-card flex flex-col rounded-2xl p-4 shadow-sm hover:border-emerald-500/30 transition-colors">
            <div class="h-40 w-full overflow-hidden rounded-xl border border-white/10 bg-white/5 mb-4 group relative">
                @if ($product->url_gambar)
                    <img class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" src="{{ $product->url_gambar }}" alt="{{ $product->nama_produk }}" />
                @else
                    <div class="flex h-full items-center justify-center text-xs text-slate-400">Tanpa foto</div>
                @endif
                @if($product->kategori)
                    <div class="absolute top-2 left-2 rounded-full bg-slate-900/80 px-2 py-1 text-[10px] font-semibold text-emerald-400 backdrop-blur">{{ $product->kategori }}</div>
                @endif
            </div>
            <div class="flex-1">
                <h2 class="text-lg font-bold line-clamp-1" title="{{ $product->nama_produk }}">{{ $product->nama_produk }}</h2>
                <div class="flex items-center gap-2 mt-1">
                    <div class="h-5 w-5 rounded-full bg-indigo-500/20 flex items-center justify-center text-[10px] text-indigo-400 font-bold border border-indigo-500/50">{{ substr($product->seller->nama_lengkap, 0, 1) }}</div>
                    <p class="text-xs text-slate-400">{{ $product->seller->nama_lengkap }}</p>
                </div>
                <p class="mt-3 text-sm text-slate-300 line-clamp-2">{{ $product->deskripsi }}</p>
            </div>
            <div class="mt-4 pt-4 border-t border-white/10 flex items-end justify-between">
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-wider">Harga</p>
                    <p class="text-lg font-bold text-emerald-400">Rp {{ number_format($product->harga, 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-400">Tersisa: {{ $product->stok }}</p>
                </div>
                <form method="POST" action="{{ route('cart.add') }}" class="flex items-center gap-2">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}" />
                    <input type="number" name="jumlah" min="1" max="{{ $product->stok }}" value="1" class="w-14 rounded-lg border border-white/10 bg-black/20 px-2 py-1.5 text-sm text-slate-100 text-center" />
                    <button class="glass-button glass-button-emerald rounded-lg p-2" type="submit" title="Tambah ke keranjang">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="col-span-full rounded-xl border border-dashed border-slate-800 p-12 text-center text-slate-400">
            <svg class="mx-auto h-12 w-12 text-slate-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            Belum ada produk untuk kategori ini.
        </div>
    @endforelse
</div>
@endsection
