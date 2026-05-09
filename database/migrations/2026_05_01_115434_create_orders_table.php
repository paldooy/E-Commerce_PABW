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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pesanan', 40)->unique();
            $table->foreignId('buyer_id')->constrained('accounts')->cascadeOnDelete();
            $table->decimal('total_harga', 18, 2);
            $table->enum('escrow_status', ['tertahan', 'dilepas', 'dikembalikan'])->default('tertahan');
            $table->timestamps();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
