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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->enum('role', ['pengguna', 'kurir', 'admin']);
            $table->string('nama_lengkap', 120);
            $table->string('username', 60)->unique();
            $table->string('email', 120)->unique();
            $table->string('no_hp', 25);
            $table->string('password_hash', 255);
            $table->boolean('status_aktif')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
