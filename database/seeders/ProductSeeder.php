<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sellers = Account::where('role', 'pengguna')->whereIn('username', ['seller1', 'seller2'])->get();
        if ($sellers->isEmpty()) return;

        $products = [
            [
                'nama_produk' => 'Sofa Minimalis Nordik',
                'kategori' => 'Furnitur',
                'deskripsi' => 'Sofa 3 dudukan dengan gaya minimalis, cocok untuk ruang tamu modern.',
                'url_gambar' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=500&q=80',
                'harga' => 3500000,
                'stok' => 5,
            ],
            [
                'nama_produk' => 'Meja Makan Kayu Jati',
                'kategori' => 'Furnitur',
                'deskripsi' => 'Meja makan elegan terbuat dari kayu jati asli.',
                'url_gambar' => 'https://images.unsplash.com/photo-1577140917170-285929fb55b7?auto=format&fit=crop&w=500&q=80',
                'harga' => 2800000,
                'stok' => 8,
            ],
            [
                'nama_produk' => 'Lampu Gantung Estetik',
                'kategori' => 'Dekorasi',
                'deskripsi' => 'Lampu gantung dengan desain industrial modern.',
                'url_gambar' => 'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?auto=format&fit=crop&w=500&q=80',
                'harga' => 450000,
                'stok' => 12,
            ],
            [
                'nama_produk' => 'Kabinet Penyimpanan Dapur',
                'kategori' => 'Dapur',
                'deskripsi' => 'Kabin fungsional serbaguna untuk peralatan dapur Anda.',
                'url_gambar' => 'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?auto=format&fit=crop&w=500&q=80',
                'harga' => 1800000,
                'stok' => 4,
            ],
            [
                'nama_produk' => 'Set Wastafel Keramik',
                'kategori' => 'Kamar Mandi',
                'deskripsi' => 'Set wastafel elegan yang meningkatkan estetika kamar mandi.',
                'url_gambar' => 'https://images.unsplash.com/photo-1584622781564-1d987f7333c1?auto=format&fit=crop&w=500&q=80',
                'harga' => 1250000,
                'stok' => 10,
            ],
            [
                'nama_produk' => 'Kursi Santai Teras',
                'kategori' => 'Taman',
                'deskripsi' => 'Kursi santai rotan tahan cuaca untuk halaman rumah.',
                'url_gambar' => 'https://images.unsplash.com/photo-1599619351208-3e6c839d6828?auto=format&fit=crop&w=500&q=80',
                'harga' => 750000,
                'stok' => 6,
            ],
            [
                'nama_produk' => 'Rak Buku Gantung',
                'kategori' => 'Furnitur',
                'deskripsi' => 'Rak buku minimalis berbentuk ambalan gantung.',
                'url_gambar' => 'https://images.unsplash.com/photo-1594620302200-9a762244a156?auto=format&fit=crop&w=500&q=80',
                'harga' => 300000,
                'stok' => 20,
            ],
            [
                'nama_produk' => 'Panci Set Anti Lengket',
                'kategori' => 'Dapur',
                'deskripsi' => 'Satu set peralatan masak lengkap dengan lapisan marble.',
                'url_gambar' => 'https://images.unsplash.com/photo-1584992236310-6edddc08acff?auto=format&fit=crop&w=500&q=80',
                'harga' => 850000,
                'stok' => 15,
            ],
            [
                'nama_produk' => 'Karpet Rajut Bohemi',
                'kategori' => 'Dekorasi',
                'deskripsi' => 'Karpet lantai estetik berbahan alami ala Bohemian.',
                'url_gambar' => 'https://images.unsplash.com/photo-1598300042247-d088f8ab3a91?auto=format&fit=crop&w=500&q=80',
                'harga' => 600000,
                'stok' => 9,
            ],
            [
                'nama_produk' => 'Pisau Dapur Set Tajam',
                'kategori' => 'Dapur',
                'deskripsi' => 'Pisau stainless steel tahan karat 5 pcs set.',
                'url_gambar' => 'https://images.unsplash.com/photo-1593618998160-e34014e67546?auto=format&fit=crop&w=500&q=80',
                'harga' => 250000,
                'stok' => 25,
            ],
            [
                'nama_produk' => 'Vas Bunga Kaca Abstrak',
                'kategori' => 'Dekorasi',
                'deskripsi' => 'Vas bunga dekoratif bahan kaca kristal.',
                'url_gambar' => 'https://images.unsplash.com/photo-1581783898377-1c85bf937427?auto=format&fit=crop&w=500&q=80',
                'harga' => 150000,
                'stok' => 18,
            ],
            [
                'nama_produk' => 'Kursi Kerja Ergonomis',
                'kategori' => 'Furnitur',
                'deskripsi' => 'Kursi kantor hidrolik anti sakit punggung.',
                'url_gambar' => 'https://images.unsplash.com/photo-1505843490538-5133c6c7d0e1?auto=format&fit=crop&w=500&q=80',
                'harga' => 1400000,
                'stok' => 7,
            ],
            [
                'nama_produk' => 'Cermin Bulat Dinding',
                'kategori' => 'Kamar Mandi',
                'deskripsi' => 'Cermin estetik dengan pigura tipis untuk wastafel.',
                'url_gambar' => 'https://images.unsplash.com/photo-1618219740975-d40978bb7378?auto=format&fit=crop&w=500&q=80',
                'harga' => 350000,
                'stok' => 11,
            ],
            [
                'nama_produk' => 'Hiasan Dinding Makrame',
                'kategori' => 'Dekorasi',
                'deskripsi' => 'Makrame rajutan tangan eksklusif.',
                'url_gambar' => 'https://images.unsplash.com/photo-1522204523234-8729aa6e3d5f?auto=format&fit=crop&w=500&q=80',
                'harga' => 200000,
                'stok' => 14,
            ],
            [
                'nama_produk' => 'Pot Tanaman Hias Keramik',
                'kategori' => 'Taman',
                'deskripsi' => 'Pot kokoh dan indah untuk tanaman indoor atau outdoor.',
                'url_gambar' => 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?auto=format&fit=crop&w=500&q=80',
                'harga' => 120000,
                'stok' => 30,
            ],
        ];

        foreach ($products as $index => $data) {
            $seller = $sellers[$index % max($sellers->count(), 1)];

            Product::create([
                'seller_id' => $seller->id,
                'nama_produk' => $data['nama_produk'],
                'kategori' => $data['kategori'],
                'deskripsi' => $data['deskripsi'],
                'url_gambar' => $data['url_gambar'],
                'harga' => $data['harga'],
                'stok' => $data['stok'],
                'status' => $data['stok'] > 0 ? 'stok_tersedia' : 'stok_kosong',
            ]);
        }
    }
}
