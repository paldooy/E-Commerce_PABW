@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold">Kelola Wallet</h1>
    <p class="text-sm text-slate-400">Tambah atau kurangi saldo wallet secara manual.</p>
</div>

<div class="space-y-4">
    @forelse ($wallets as $wallet)
        <div class="glass-card rounded-2xl p-4 shadow-sm">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-lg font-semibold">{{ $wallet->account->nama_lengkap }}</p>
                    <p class="text-sm text-slate-400">{{ $wallet->nomor_dompet }}</p>
                    <p class="text-xs text-slate-500">Saldo: Rp {{ number_format($wallet->saldo, 0, ',', '.') }}</p>
                </div>
                <form method="POST" action="{{ route('admin.wallets.update', $wallet) }}" class="flex flex-wrap items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <select name="aksi" class="rounded-lg border border-white/10 bg-white/5 px-2 py-1 text-sm text-slate-100">
                        <option value="tambah">Tambah</option>
                        <option value="kurang">Kurang</option>
                    </select>
                    <input type="number" name="nominal" min="1" class="w-32 rounded-lg border border-white/10 bg-white/5 px-2 py-1 text-sm text-slate-100" placeholder="Nominal" required />
                    <button class="glass-button rounded-lg px-3 py-1 text-sm" type="submit">Proses</button>
                </form>
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-dashed border-slate-800 p-6 text-center text-slate-400">Belum ada wallet.</div>
    @endforelse
</div>
@endsection
