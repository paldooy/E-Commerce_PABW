<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'max:60', Rule::unique('accounts', 'username')->withoutTrashed()],
            'email' => ['required', 'email', 'max:120', Rule::unique('accounts', 'email')->withoutTrashed()],
            'no_hp' => ['required', 'string', 'max:25'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
        // Bersihkan data sampah yang menyebabkan bentrok di level database
        $trashedAccounts = Account::onlyTrashed()
            ->where('username', $data['username'])
            ->orWhere('email', $data['email'])
            ->get();

        foreach ($trashedAccounts as $trashed) {
            $trashed->username = $trashed->username . '_del_' . $trashed->id;
            $trashed->email = 'del_' . $trashed->id . '_' . $trashed->email;
            $trashed->save();
        }
        $account = Account::create([
            'role' => 'pengguna',
            'nama_lengkap' => $data['nama_lengkap'],
            'username' => $data['username'],
            'email' => $data['email'],
            'no_hp' => $data['no_hp'],
            'password_hash' => Hash::make($data['password']),
            'status_aktif' => true,
        ]);

        Wallet::create([
            'account_id' => $account->id,
            'nomor_dompet' => 'WALLET-' . strtoupper(Str::random(8)),
            'saldo' => 0,
        ]);

        return redirect()->route('login')->with('success', 'Registrasi berhasil. Silakan login.');
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $account = Account::where('username', $data['username'])->first();

        if (!$account || !Hash::check($data['password'], $account->password_hash)) {
            return back()->withErrors(['username' => 'Username atau password salah.'])->withInput();
        }

        if (!$account->status_aktif) {
            return back()->withErrors(['username' => 'Akun tidak aktif.'])->withInput();
        }

        session(['account_id' => $account->id]);

        $redirect = redirect()->route('dashboard');

        if ($request->has('remember_me')) {
            $token = Str::random(60);
            $account->update(['remember_token' => $token]);
            $cookieValue = json_encode(['id' => $account->id, 'token' => $token]);
            $redirect->cookie('remember_account', $cookieValue, 60 * 24 * 30); // 30 days
        }

        return $redirect;
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('account_id');

        $redirect = redirect()->route('login');
        if (\Illuminate\Support\Facades\Cookie::has('remember_account')) {
            $redirect->withoutCookie('remember_account');
        }

        return $redirect;
    }
}
