@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-semibold">Kelola Pengguna</h1>
        <p class="text-sm text-slate-400">Manajemen akun pengguna.</p>
    </div>
    <a class="glass-button glass-button-emerald rounded-lg px-4 py-2 text-sm font-semibold" href="{{ route('admin.users.create') }}">Tambah Pengguna</a>
</div>

<div class="space-y-3">
    @forelse ($users as $user)
        <div class="glass-card flex flex-col gap-2 rounded-2xl p-4 shadow-sm md:flex-row md:items-center md:justify-between">
            <div>
                <p class="font-semibold">{{ $user->nama_lengkap }}</p>
                <p class="text-sm text-slate-400">{{ $user->email }} | {{ $user->username }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="rounded-full border border-white/10 bg-white/5 px-2 py-1 text-xs text-slate-200">{{ $user->status_aktif ? 'Aktif' : 'Nonaktif' }}</span>
                <a class="glass-button rounded-lg px-3 py-1 text-sm" href="{{ route('admin.users.edit', $user) }}">Edit</a>
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                    @csrf
                    @method('DELETE')
                    <button class="glass-button rounded-lg border border-rose-500/40 px-3 py-1 text-sm text-rose-200" type="submit">Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-dashed border-slate-800 p-6 text-center text-slate-400">Belum ada pengguna.</div>
    @endforelse
</div>
@endsection
