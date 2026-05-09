@extends('layouts.app')

@section('content')
<div class="glass-card mx-auto max-w-xl rounded-2xl p-6 shadow-sm">
    <h1 class="mb-4 text-2xl font-semibold">Tambah Kurir</h1>
    <form method="POST" action="{{ route('admin.couriers.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="text-sm font-medium text-slate-300">Nama Lengkap</label>
            <input name="nama_lengkap" class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100" required />
        </div>
        <div>
            <label class="text-sm font-medium text-slate-300">Username</label>
            <input name="username" class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100" required />
        </div>
        <div>
            <label class="text-sm font-medium text-slate-300">Email</label>
            <input type="email" name="email" class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100" required />
        </div>
        <div>
            <label class="text-sm font-medium text-slate-300">Nomor HP</label>
            <input name="no_hp" class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100" required />
        </div>
        <div>
            <label class="text-sm font-medium text-slate-300">Password</label>
            <input type="password" name="password" class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100" required />
        </div>
        <div class="flex items-center gap-2">
            <input type="hidden" name="status_aktif" value="0" />
            <input type="checkbox" name="status_aktif" value="1" checked />
            <label class="text-sm text-slate-300">Aktif</label>
        </div>
        <button class="glass-button glass-button-emerald w-full rounded-lg px-4 py-2 font-semibold" type="submit">Simpan</button>
    </form>
</div>
@endsection
