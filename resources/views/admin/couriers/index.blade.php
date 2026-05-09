@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-semibold">Kelola Kurir</h1>
        <p class="text-sm text-slate-400">Manajemen akun kurir.</p>
    </div>
    <a class="glass-button glass-button-emerald rounded-lg px-4 py-2 text-sm font-semibold" href="{{ route('admin.couriers.create') }}">Tambah Kurir</a>
</div>

<div class="space-y-3">
    @forelse ($couriers as $courier)
        <div class="glass-card flex flex-col gap-2 rounded-2xl p-4 shadow-sm md:flex-row md:items-center md:justify-between">
            <div>
                <p class="font-semibold">{{ $courier->nama_lengkap }}</p>
                <p class="text-sm text-slate-400">{{ $courier->email }} | {{ $courier->username }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="rounded-full border border-white/10 bg-white/5 px-2 py-1 text-xs text-slate-200">{{ $courier->status_aktif ? 'Aktif' : 'Nonaktif' }}</span>
                <a class="glass-button rounded-lg px-3 py-1 text-sm" href="{{ route('admin.couriers.edit', $courier) }}">Edit</a>
                <form method="POST" action="{{ route('admin.couriers.destroy', $courier) }}">
                    @csrf
                    @method('DELETE')
                    <button class="glass-button rounded-lg border border-rose-500/40 px-3 py-1 text-sm text-rose-200" type="submit">Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-dashed border-slate-800 p-6 text-center text-slate-400">Belum ada kurir.</div>
    @endforelse
</div>
@endsection
