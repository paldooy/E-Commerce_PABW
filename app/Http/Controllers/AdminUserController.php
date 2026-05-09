<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $admin = $this->requireRole(['admin']);
        $users = Account::where('role', 'pengguna')->orderByDesc('created_at')->get();

        return view('admin.users.index', [
            'admin' => $admin,
            'users' => $users,
        ]);
    }

    public function create(): View
    {
        $admin = $this->requireRole(['admin']);

        return view('admin.users.create', [
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

        $user = Account::create([
            'role' => 'pengguna',
            'nama_lengkap' => $data['nama_lengkap'],
            'username' => $data['username'],
            'email' => $data['email'],
            'no_hp' => $data['no_hp'],
            'password_hash' => Hash::make($data['password']),
            'status_aktif' => $data['status_aktif'] ?? true,
        ]);

        Wallet::create([
            'account_id' => $user->id,
            'nomor_dompet' => 'WALLET-' . strtoupper(Str::random(8)),
            'saldo' => 0,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(Account $user): View
    {
        $admin = $this->requireRole(['admin']);

        if ($user->role !== 'pengguna') {
            abort(404);
        }

        return view('admin.users.edit', [
            'admin' => $admin,
            'user' => $user,
        ]);
    }

    public function update(Request $request, Account $user): RedirectResponse
    {
        $this->requireRole(['admin']);

        if ($user->role !== 'pengguna') {
            abort(404);
        }

        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', 'unique:accounts,email,' . $user->id],
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

        $user->update($payload);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(Account $user): RedirectResponse
    {
        $this->requireRole(['admin']);

        if ($user->role !== 'pengguna') {
            abort(404);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
