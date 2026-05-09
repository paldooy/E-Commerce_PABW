<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
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

        $cart = Cart::create([
            'buyer_id' => $buyer->id,
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'jumlah' => 1,
        ]);
    }
}
