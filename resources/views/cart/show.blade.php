@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-semibold">Keranjang Belanja</h1>
        <p class="text-sm text-slate-400">Kelola barang yang akan dibeli.</p>
    </div>
    <a class="glass-button rounded-lg px-3 py-2 text-sm" href="{{ route('checkout.show') }}">Checkout</a>
</div>

<div class="space-y-4">
    @forelse ($cart->items as $item)
        <div class="glass-card flex flex-col gap-4 rounded-2xl p-4 shadow-sm md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold">{{ $item->product->nama_produk }}</h2>
                <p class="text-sm text-slate-400">Harga: Rp {{ number_format($item->product->harga, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500">Stok tersedia: {{ $item->product->stok }}</p>
            </div>
            <div class="flex flex-col gap-2 md:items-end">
                <form method="POST" action="{{ route('cart.update', $item) }}" class="flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <input type="number" name="jumlah" min="0" max="{{ $item->product->stok }}" value="{{ $item->jumlah }}" class="w-20 rounded-lg border border-white/10 bg-white/5 px-2 py-1 text-slate-100" />
                    <button class="glass-button rounded-lg px-3 py-1 text-sm" type="submit">Update</button>
                </form>
                <form method="POST" action="{{ route('cart.remove', $item) }}">
                    @csrf
                    @method('DELETE')
                    <button class="text-sm text-rose-300 hover:text-rose-200" type="submit">Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-dashed border-slate-800 p-6 text-center text-slate-400">Keranjang masih kosong.</div>
    @endforelse
</div>
@endsection
