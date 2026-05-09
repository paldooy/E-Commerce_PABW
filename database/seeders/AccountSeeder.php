<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Account::create([
            'role' => 'admin',
            'nama_lengkap' => 'Admin Utama',
            'username' => 'admin',
            'email' => 'admin@example.test',
            'no_hp' => '080000000000',
            'password_hash' => Hash::make('password'),
            'status_aktif' => true,
        ]);

        Account::create([
            'role' => 'kurir',
            'nama_lengkap' => 'Kurir Utama',
            'username' => 'kurir1',
            'email' => 'kurir1@example.test',
            'no_hp' => '080000000001',
            'password_hash' => Hash::make('password'),
            'status_aktif' => true,
        ]);

        Account::create([
            'role' => 'pengguna',
            'nama_lengkap' => 'Seller Alpha',
            'username' => 'seller1',
            'email' => 'seller1@example.test',
            'no_hp' => '080000000010',
            'password_hash' => Hash::make('password'),
            'status_aktif' => true,
        ]);

        Account::create([
            'role' => 'pengguna',
            'nama_lengkap' => 'Seller Beta',
            'username' => 'seller2',
            'email' => 'seller2@example.test',
            'no_hp' => '080000000011',
            'password_hash' => Hash::make('password'),
            'status_aktif' => true,
        ]);

        Account::create([
            'role' => 'pengguna',
            'nama_lengkap' => 'Buyer Utama',
            'username' => 'buyer1',
            'email' => 'buyer1@example.test',
            'no_hp' => '080000000020',
            'password_hash' => Hash::make('password'),
            'status_aktif' => true,
        ]);
    }
}
