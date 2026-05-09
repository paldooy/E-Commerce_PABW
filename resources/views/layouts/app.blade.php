<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'HomeSupply.co' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    @php
        $currentAccount = $currentAccount ?? $buyer ?? $seller ?? $admin ?? $courier ?? $account ?? null;
    @endphp
    <header class="border-b border-slate-800 bg-slate-950/80 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/80 shadow-lg shadow-emerald-500/30 text-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-extrabold tracking-tight">HomeSupply<span class="text-emerald-400">.co</span></p>
                    <p class="text-xs text-slate-400">Marketplace Perlengkapan Rumah Zaman Now</p>
                </div>
            </div>
            <div class="flex items-center gap-3 text-sm">
                @if($currentAccount)
                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-slate-200">
                        {{ $currentAccount->nama_lengkap }} ({{ $currentAccount->role }})
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="glass-button rounded-lg px-3 py-1" type="submit">Logout</button>
                    </form>
                @else
                    <a class="glass-button rounded-lg px-3 py-1" href="{{ route('login') }}">Login</a>
                @endif
            </div>
        </div>
        <nav class="border-t border-slate-800 bg-slate-950/80 backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 text-sm">
                <!-- Kiri: Menu Utama -->
                <div class="flex items-center gap-4">
                    <a class="text-slate-300 hover:text-emerald-400 font-medium transition-colors" href="{{ route('dashboard') }}">Beranda</a>
                    <a class="text-slate-300 hover:text-emerald-400 font-medium transition-colors" href="{{ route('catalog.index') }}">Katalog</a>
                    @if($currentAccount && $currentAccount->role === 'pengguna')
                        <a class="text-slate-300 hover:text-emerald-400 font-medium transition-colors" href="{{ route('seller.products.index') }}">Produk Saya</a>
                        <a class="text-slate-300 hover:text-emerald-400 font-medium transition-colors" href="{{ route('orders.index') }}">Pesanan</a>
                    @endif
                    @if($currentAccount && $currentAccount->role === 'kurir')
                        <a class="text-slate-300 hover:text-emerald-400 font-medium transition-colors" href="{{ route('courier.index') }}">Tugas Kurir</a>
                    @endif
                    @if($currentAccount && $currentAccount->role === 'admin')
                        <a class="text-slate-300 hover:text-emerald-400 font-medium transition-colors" href="{{ route('admin.users.index') }}">Admin Users</a>
                        <a class="text-slate-300 hover:text-emerald-400 font-medium transition-colors" href="{{ route('admin.couriers.index') }}">Admin Kurir</a>
                        <a class="text-slate-300 hover:text-emerald-400 font-medium transition-colors" href="{{ route('admin.products.index') }}">Admin Produk</a>
                        <a class="text-slate-300 hover:text-emerald-400 font-medium transition-colors" href="{{ route('admin.wallets.index') }}">Admin Wallet</a>
                    @endif
                </div>

                <!-- Kanan: Keranjang & Dompet -->
                <div class="flex items-center gap-6">
                    @if($currentAccount && $currentAccount->role === 'pengguna')
                        @php
                            $cart = \App\Models\Cart::where('buyer_id', $currentAccount->id)->first();
                            $cartCount = $cart ? $cart->items()->sum('jumlah') : 0;
                            $wallet = \App\Models\Wallet::where('account_id', $currentAccount->id)->first();
                            $balance = $wallet ? $wallet->saldo : 0;
                        @endphp
                        <a href="{{ route('cart.show') }}" class="relative text-slate-300 hover:text-emerald-400 transition-colors flex items-center">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            @if($cartCount > 0)
                                <span class="absolute -top-2 -right-2 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white">{{ $cartCount }}</span>
                            @endif
                        </a>

                        <div class="flex items-center gap-2 text-slate-300 border-l border-slate-700 pl-4">
                            <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <span class="font-semibold text-emerald-400">Rp {{ number_format($balance, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </nav>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
        @if (session('success'))
            <div class="mb-4 rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-emerald-200">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-rose-200">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
