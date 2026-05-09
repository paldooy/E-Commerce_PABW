@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold">Checkout</h1>
    <p class="text-sm text-slate-400">Periksa total belanja sebelum membayar.</p>
</div>

<div class="glass-card rounded-2xl p-6 shadow-sm">
    <div class="space-y-3">
        @forelse ($cart->items as $item)
            <div class="flex items-center justify-between text-sm">
                <div>
                    <p class="font-medium">{{ $item->product->nama_produk }}</p>
                    <p class="text-slate-400">{{ $item->jumlah }} x Rp {{ number_format($item->product->harga, 0, ',', '.') }}</p>
                </div>
                <p class="font-semibold">Rp {{ number_format($item->product->harga * $item->jumlah, 0, ',', '.') }}</p>
            </div>
        @empty
            <p class="text-slate-400">Keranjang kosong.</p>
        @endforelse
    </div>
    <div class="mt-6 border-t border-slate-800 pt-4">
        <div class="flex items-center justify-between text-lg font-semibold">
            <span>Total</span>
            <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
        </div>
    </div>
    <form method="POST" action="{{ route('checkout.process') }}" class="mt-6">
        @csrf
        <button class="glass-button glass-button-emerald w-full rounded-lg px-4 py-2 font-semibold" type="submit">Bayar Sekarang</button>
    </form>
</div>
@endsection
