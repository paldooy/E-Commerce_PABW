<?php

namespace App\Http\Controllers;

use App\Models\Account;

abstract class Controller
{
    protected function currentAccount(): ?Account
    {
        $accountId = session('account_id');

        if (!$accountId) {
            $rememberToken = json_decode(\Illuminate\Support\Facades\Cookie::get('remember_account', 'null'), true);
            if ($rememberToken && isset($rememberToken['id']) && isset($rememberToken['token'])) {
                $account = Account::where('id', $rememberToken['id'])
                    ->where('remember_token', $rememberToken['token'])
                    ->first();
                if ($account) {
                    session(['account_id' => $account->id]);
                    return $account;
                }
            }
            return null;
        }

        return Account::find($accountId);
    }

    protected function requireRole(array $roles): Account
    {
        $account = $this->currentAccount();

        if (!$account || !in_array($account->role, $roles, true)) {
            abort(403);
        }

        return $account;
    }
}
