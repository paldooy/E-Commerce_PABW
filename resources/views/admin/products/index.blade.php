@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold">Moderasi Produk</h1>
    <p class="text-sm text-slate-400">Pantau dan ubah status listing produk.</p>
</div>

<div class="space-y-4">
    @forelse ($products as $product)
        <div class="glass-card flex flex-col gap-3 rounded-2xl p-4 shadow-sm md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-lg font-semibold">{{ $product->nama_produk }}</p>
                <p class="text-sm text-slate-400">Penjual: {{ $product->seller->nama_lengkap }}</p>
                <p class="text-xs text-slate-500">Stok: {{ $product->stok }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <form method="POST" action="{{ route('admin.products.update', $product) }}" class="flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="rounded-lg border border-white/10 bg-white/5 px-2 py-1 text-sm text-slate-100">
                        <option value="stok_tersedia" {{ $product->status === 'stok_tersedia' ? 'selected' : '' }}>stok_tersedia</option>
                        <option value="stok_kosong" {{ $product->status === 'stok_kosong' ? 'selected' : '' }}>stok_kosong</option>
                    </select>
                    <button class="glass-button rounded-lg px-3 py-1 text-sm" type="submit">Update</button>
                </form>
                <form method="POST" action="{{ route('admin.products.destroy', $product) }}">
                    @csrf
                    @method('DELETE')
                    <button class="glass-button rounded-lg border border-rose-500/40 px-3 py-1 text-sm text-rose-200" type="submit">Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-dashed border-slate-800 p-6 text-center text-slate-400">Belum ada produk.</div>
    @endforelse
</div>
@endsection
