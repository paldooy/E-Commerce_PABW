@extends('layouts.app')

@section('content')
<!-- Section 1: Hero Banner -->
<div class="relative overflow-hidden rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl mb-8">
    <div class="absolute inset-0 z-0">
        <!-- Foto aesthetic, ditambahkan blur dan opacity filter: -->
        <img src="https://images.unsplash.com/photo-1616486029423-aaa4789e8c9a?q=80&w=2000&auto=format&fit=crop" alt="Home Decor" class="w-full h-full object-cover opacity-40 blur-sm pointer-events-none select-none">
    </div>
    <div class="relative z-10 flex flex-col items-center justify-center py-20 px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4 drop-shadow-md tracking-tight">Selamat Datang di HomeSupply.co, {{ $account->nama_lengkap }}</h1>
        <p class="text-lg md:text-xl text-slate-300 mb-8 max-w-2xl drop-shadow">Penuhi kebutuhan perlengkapan rumah Anda, atau jual barang berlebih Anda dengan sistem yang aman dan terpercaya.</p>
        <div class="flex flex-wrap items-center justify-center gap-4">
            <a href="{{ route('catalog.index') }}" class="glass-button glass-button-emerald px-6 py-3 rounded-xl font-bold text-emerald-50 transition-transform hover:scale-105 shadow-emerald-500/20 shadow-lg">
                👉 Mulai Belanja
            </a>
            <a href="{{ route('seller.products.index') }}" class="glass-button px-6 py-3 rounded-xl font-bold text-slate-100 transition-transform hover:scale-105">
                📦 Jual Barang
            </a>
        </div>
    </div>
</div>

<!-- Section 2: Ringkasan Aktivitas (Quick Stats) -->
<div class="mb-8">
    <h2 class="text-xl font-semibold mb-4 text-slate-200">Aktivitas Anda Saat Ini</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Pesanan Aktif (Membeli) -->
        <a href="{{ route('orders.index', ['tab' => 'pembelian']) }}" class="glass-card flex items-center p-5 rounded-2xl transition hover:border-emerald-500/50 group">
            <div class="flex-shrink-0 mr-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400 group-hover:bg-emerald-500/30 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
            </div>
            <div>
                <p class="text-2xl font-bold text-white">{{ $buying_active }}</p>
                <p class="text-sm font-medium text-slate-400">Pesanan Aktif (Ditunggu)</p>
            </div>
        </a>

        <!-- Perlu Diproses (Menjual) -->
        <a href="{{ route('orders.index', ['tab' => 'penjualan']) }}" class="glass-card flex items-center p-5 rounded-2xl transition hover:border-rose-500/50 group">
            <div class="flex-shrink-0 mr-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-500/20 text-rose-400 group-hover:bg-rose-500/30 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div>
                <p class="text-2xl font-bold text-white">{{ $selling_active }}</p>
                <p class="text-sm font-medium text-slate-400">Pesanan Masuk (Perlu Diproses)</p>
            </div>
        </a>
    </div>
</div>

<!-- Section 3: Produk Terbaru Seluruh Pengguna -->
<div class="mb-8">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-slate-200">Barang Baru Dirilis 🚀</h2>
        <a href="{{ route('catalog.index') }}" class="text-sm text-emerald-400 font-medium hover:underline">Lihat Semua di Katalog ➔</a>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        @forelse ($latest_products as $product)
            <div class="glass-card flex flex-col overflow-hidden rounded-2xl group hover:border-emerald-500/50 transition-colors">
                <a href="{{ route('catalog.index') }}?search={{ urlencode($product->nama_produk) }}" class="block">
                    @if ($product->url_gambar)
                        <img src="{{ $product->url_gambar }}" alt="{{ $product->nama_produk }}" class="h-40 w-full object-cover transition-transform duration-300 group-hover:scale-105" />
                    @else
                        <div class="flex h-40 w-full items-center justify-center bg-white/5 text-slate-500 transition-transform duration-300 group-hover:scale-105">No Image</div>
                    @endif
                </a>
                <div class="flex flex-1 flex-col p-4">
                    <h3 class="mb-1 text-sm font-semibold text-slate-200 line-clamp-2">{{ $product->nama_produk }}</h3>
                    <p class="mb-3 text-lg font-bold text-emerald-400 leading-none">Rp {{ number_format($product->harga, 0, ',', '.') }}</p>
                    <div class="mt-auto">
                        <span class="inline-block rounded-full bg-slate-800/80 px-2 py-1 text-[10px] text-slate-400 line-clamp-1 border border-slate-700/50">
                            {{ $product->kategori ?? 'Umum' }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-2 md:col-span-5 rounded-2xl border border-dashed border-slate-800 p-8 text-center text-slate-400">
                Belum ada produk baru terpajang.
            </div>
        @endforelse
    </div>
</div>

<!-- Section 4: Seller Corner -->
<div class="glass-card relative overflow-hidden rounded-3xl border border-slate-800 p-8 flex flex-col md:flex-row items-center justify-between gap-6">
    <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>
    <div class="z-10 text-center md:text-left max-w-2xl">
        <h3 class="text-2xl font-bold text-white mb-2">Punya barang tidak terpakai?</h3>
        <p class="text-slate-300 text-lg">Jadikan uang tunai sekarang dengan sistem Escrow yang menjamin keamanan transaksi antar pengguna.</p>
    </div>
    <div class="z-10 flex-shrink-0">
        <a href="{{ route('seller.products.create') }}" class="glass-button glass-button-emerald px-8 py-4 rounded-full font-bold text-white shadow-lg whitespace-nowrap block text-center">
            Mulai Berjualan Sekarang
        </a>
    </div>
</div>
@endsection