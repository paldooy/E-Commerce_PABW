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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('accounts')->cascadeOnDelete();
            $table->string('kategori', 100);
            $table->string('nama_produk', 150);
            $table->text('deskripsi');
            $table->text('url_gambar')->nullable();
            $table->decimal('harga', 18, 2);
            $table->integer('stok');
            $table->enum('status', ['stok_kosong', 'stok_tersedia'])->default('stok_kosong');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
