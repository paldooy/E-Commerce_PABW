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
        Schema::create('order_item_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->enum('status_lama', [
                'menunggu_penjual',
                'diproses_penjual',
                'menunggu_kurir',
                'sedang_dikirim',
                'sampai_di_tujuan',
                'diterima_pembeli',
                'dikomplain',
                'dikirim_balik',
                'transaksi_gagal',
            ]);
            $table->enum('status_baru', [
                'menunggu_penjual',
                'diproses_penjual',
                'menunggu_kurir',
                'sedang_dikirim',
                'sampai_di_tujuan',
                'diterima_pembeli',
                'dikomplain',
                'dikirim_balik',
                'transaksi_gagal',
            ]);
            $table->foreignId('diubah_oleh_account_id')->constrained('accounts')->cascadeOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_status_logs');
    }
};
