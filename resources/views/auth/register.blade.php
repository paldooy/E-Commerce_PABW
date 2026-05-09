@extends('layouts.guest')

@section('content')
<div class="mb-6 text-center">
    <h2 class="text-2xl font-bold text-white mb-2">Buat Akun Baru</h2>
    <p class="text-slate-400 text-sm">Bergabunglah dengan komunitas HomeSupply untuk mulai bertransaksi.</p>
</div>

<form method="POST" action="{{ route('register.submit') }}" class="space-y-4">
    @csrf
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Nama Lengkap</label>
            <input name="nama_lengkap" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:border-emerald-500/50 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 transition-colors" value="{{ old('nama_lengkap') }}" placeholder="John Doe" required />
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Username</label>
            <input name="username" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:border-emerald-500/50 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 transition-colors" value="{{ old('username') }}" placeholder="johndoe" required />
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-300 mb-1">Email</label>
        <input type="email" name="email" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:border-emerald-500/50 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 transition-colors" value="{{ old('email') }}" placeholder="john@example.com" required />
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-300 mb-1">Nomor HP</label>
        <input name="no_hp" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:border-emerald-500/50 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 transition-colors" value="{{ old('no_hp') }}" placeholder="08123456789" required />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Password</label>
            <input type="password" name="password" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:border-emerald-500/50 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 transition-colors" placeholder="••••••••" required />
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:border-emerald-500/50 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 transition-colors" placeholder="••••••••" required />
        </div>
    </div>

    <button class="glass-button glass-button-emerald w-full rounded-xl px-4 py-3 font-bold text-white shadow-lg mt-6 transition-transform hover:scale-[1.02]" type="submit">Daftar Akun</button>
</form>

<p class="mt-8 text-center text-sm text-slate-400">
    Sudah punya akun? <a class="font-semibold text-emerald-400 hover:underline" href="{{ route('login') }}">Masuk di sini</a>
</p>
@endsection
