<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('accounts')->cascadeOnDelete();
            $table->string('nama_produk_snapshot', 150);
            $table->text('deskripsi_produk_snapshot');
            $table->text('gambar_produk_snapshot')->nullable();
            $table->decimal('harga_satuan_snapshot', 18, 2);
            $table->integer('jumlah');
            $table->decimal('subtotal', 18, 2);
            $table->enum('status', [
                'menunggu_penjual',
                'diproses_penjual',
                'menunggu_kurir',
                'sedang_dikirim',
                'sampai_di_tujuan',
                'diterima_pembeli',
                'dikomplain',
                'dikirim_balik',
                'transaksi_gagal',
            ])->default('menunggu_penjual');
            $table->text('alasan_komplain')->nullable();
            $table->timestamps();
            $table->timestamp('failed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
