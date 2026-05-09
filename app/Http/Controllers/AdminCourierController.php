<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminCourierController extends Controller
{
    public function index(): View
    {
        $admin = $this->requireRole(['admin']);
        $couriers = Account::where('role', 'kurir')->orderByDesc('created_at')->get();

        return view('admin.couriers.index', [
            'admin' => $admin,
            'couriers' => $couriers,
        ]);
    }

    public function create(): View
    {
        $admin = $this->requireRole(['admin']);

        return view('admin.couriers.create', [
            'admin' => $admin,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requireRole(['admin']);

        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'max:60', 'unique:accounts,username'],
            'email' => ['required', 'email', 'max:120', 'unique:accounts,email'],
            'no_hp' => ['required', 'string', 'max:25'],
            'password' => ['required', 'string', 'min:6'],
            'status_aktif' => ['nullable', 'boolean'],
        ]);

        $courier = Account::create([
            'role' => 'kurir',
            'nama_lengkap' => $data['nama_lengkap'],
            'username' => $data['username'],
            'email' => $data['email'],
            'no_hp' => $data['no_hp'],
            'password_hash' => Hash::make($data['password']),
            'status_aktif' => $data['status_aktif'] ?? true,
        ]);

        Wallet::create([
            'account_id' => $courier->id,
            'nomor_dompet' => 'WALLET-' . strtoupper(Str::random(8)),
            'saldo' => 0,
        ]);

        return redirect()->route('admin.couriers.index')->with('success', 'Kurir berhasil ditambahkan.');
    }

    public function edit(Account $courier): View
    {
        $admin = $this->requireRole(['admin']);

        if ($courier->role !== 'kurir') {
            abort(404);
        }

        return view('admin.couriers.edit', [
            'admin' => $admin,
            'courier' => $courier,
        ]);
    }

    public function update(Request $request, Account $courier): RedirectResponse
    {
        $this->requireRole(['admin']);

        if ($courier->role !== 'kurir') {
            abort(404);
        }

        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', 'unique:accounts,email,' . $courier->id],
            'no_hp' => ['required', 'string', 'max:25'],
            'status_aktif' => ['required', 'boolean'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $payload = [
            'nama_lengkap' => $data['nama_lengkap'],
            'email' => $data['email'],
            'no_hp' => $data['no_hp'],
            'status_aktif' => $data['status_aktif'],
        ];

        if (!empty($data['password'])) {
            $payload['password_hash'] = Hash::make($data['password']);
        }

        $courier->update($payload);

        return redirect()->route('admin.couriers.index')->with('success', 'Kurir berhasil diperbarui.');
    }

    public function destroy(Account $courier): RedirectResponse
    {
        $this->requireRole(['admin']);

        if ($courier->role !== 'kurir') {
            abort(404);
        }

        $courier->delete();

        return redirect()->route('admin.couriers.index')->with('success', 'Kurir berhasil dihapus.');
    }
}
