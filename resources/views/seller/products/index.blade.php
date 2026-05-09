@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-semibold">Etalase Produk</h1>
        <p class="text-sm text-slate-400">Kelola produk jualan Anda.</p>
    </div>
    <a class="glass-button glass-button-emerald rounded-lg px-4 py-2 text-sm font-semibold" href="{{ route('seller.products.create') }}">Tambah Produk</a>
</div>

<div class="space-y-4">
    @forelse ($products as $product)
        <div class="glass-card rounded-2xl p-4 shadow-sm">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-lg font-semibold">{{ $product->nama_produk }}</p>
                    <p class="text-sm text-slate-400">Stok: {{ $product->stok }} | Status: {{ $product->status }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a class="glass-button rounded-lg px-3 py-1 text-sm" href="{{ route('seller.products.edit', $product) }}">Edit</a>
                    <form method="POST" action="{{ route('seller.products.destroy', $product) }}">
                        @csrf
                        @method('DELETE')
                        <button class="glass-button rounded-lg border border-rose-500/40 px-3 py-1 text-sm text-rose-200" type="submit">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-dashed border-slate-800 p-6 text-center text-slate-400">Belum ada produk.</div>
    @endforelse
</div>
@endsection
