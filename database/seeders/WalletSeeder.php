<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Wallet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = Account::all();

        foreach ($accounts as $account) {
            Wallet::create([
                'account_id' => $account->id,
                'nomor_dompet' => 'WALLET-' . str_pad((string) $account->id, 6, '0', STR_PAD_LEFT),
                'saldo' => $account->role === 'admin' ? 0 : 1000000,
            ]);
        }
    }
}
