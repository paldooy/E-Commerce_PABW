<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminWalletController extends Controller
{
    public function index(): View
    {
        $admin = $this->requireRole(['admin']);
        $wallets = Wallet::with('account')->orderBy('id')->get();

        return view('admin.wallets.index', [
            'admin' => $admin,
            'wallets' => $wallets,
        ]);
    }

    public function update(Request $request, Wallet $wallet): RedirectResponse
    {
        $this->requireRole(['admin']);

        $data = $request->validate([
            'aksi' => ['required', 'in:tambah,kurang'],
            'nominal' => ['required', 'numeric', 'min:1'],
        ]);

        if ($data['aksi'] === 'kurang' && $wallet->saldo < $data['nominal']) {
            return back()->withErrors(['nominal' => 'Saldo tidak mencukupi untuk dikurangi.']);
        }

        $wallet->saldo = $data['aksi'] === 'tambah'
            ? $wallet->saldo + $data['nominal']
            : $wallet->saldo - $data['nominal'];
        $wallet->save();

        return back()->with('success', 'Saldo berhasil diperbarui.');
    }
}
