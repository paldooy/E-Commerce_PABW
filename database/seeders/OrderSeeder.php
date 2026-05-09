<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemStatusLog;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buyer = Account::where('username', 'buyer1')->first();
        $product = Product::first();

        if (!$buyer || !$product) {
            return;
        }

        $order = Order::create([
            'nomor_pesanan' => 'ORD-' . strtoupper(Str::random(8)),
            'buyer_id' => $buyer->id,
            'total_harga' => $product->harga,
            'escrow_status' => 'tertahan',
            'paid_at' => Carbon::now(),
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'seller_id' => $product->seller_id,
            'nama_produk_snapshot' => $product->nama_produk,
            'deskripsi_produk_snapshot' => $product->deskripsi,
            'gambar_produk_snapshot' => $product->url_gambar,
            'harga_satuan_snapshot' => $product->harga,
            'jumlah' => 1,
            'subtotal' => $product->harga,
            'status' => 'menunggu_penjual',
        ]);

        OrderItemStatusLog::create([
            'order_item_id' => $item->id,
            'status_lama' => 'menunggu_penjual',
            'status_baru' => 'menunggu_penjual',
            'diubah_oleh_account_id' => $buyer->id,
            'catatan' => 'Pesanan dibuat oleh pembeli.',
            'created_at' => Carbon::now(),
        ]);
    }
}
