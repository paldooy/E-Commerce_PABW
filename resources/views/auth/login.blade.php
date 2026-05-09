@extends('layouts.guest')

@section('content')
<div class="mb-6 text-center">
    <h2 class="text-2xl font-bold text-white mb-2">Selamat Datang Kembali!</h2>
    <p class="text-slate-400 text-sm">Masuk untuk melanjutkan aktivitas berbelanja atau berjualan Anda.</p>
</div>

<form method="POST" action="{{ route('login.submit') }}" class="space-y-5">
    @csrf
    <div>
        <label class="block text-sm font-medium text-slate-300 mb-1">Username</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            </div>
            <input name="username" class="w-full rounded-xl border border-white/10 bg-white/5 pl-10 pr-4 py-2.5 text-slate-100 placeholder-slate-500 focus:border-emerald-500/50 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 transition-colors" value="{{ old('username') }}" placeholder="Masukkan username" required />
        </div>
    </div>
    
    <div>
        <label class="block text-sm font-medium text-slate-300 mb-1">Password</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
            </div>
            <input type="password" name="password" class="w-full rounded-xl border border-white/10 bg-white/5 pl-10 pr-4 py-2.5 text-slate-100 placeholder-slate-500 focus:border-emerald-500/50 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 transition-colors" placeholder="••••••••" required />
        </div>
    </div>

    <div class="flex items-center mt-4">
        <label class="flex items-center text-sm text-slate-400 cursor-pointer hover:text-slate-300 relative">
            <input type="checkbox" name="remember_me" value="1" class="peer sr-only">
            <div class="h-5 w-5 rounded border border-white/20 bg-white/5 peer-checked:bg-emerald-500 peer-checked:border-emerald-500 transition-colors mr-2 flex items-center justify-center">
                <svg class="h-3.5 w-3.5 text-white opacity-0 peer-checked:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            Ingat Saya (Remind Me)
        </label>
    </div>

    <button class="glass-button glass-button-emerald w-full rounded-xl px-4 py-3 font-bold text-white shadow-lg mt-2 transition-transform hover:scale-[1.02]" type="submit">Masuk</button>
</form>

<p class="mt-8 text-center text-sm text-slate-400">
    Belum punya akun? <a class="font-semibold text-emerald-400 hover:underline" href="{{ route('register') }}">Daftar Sekarang</a>
</p>
@endsection
