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
        Schema::create('peminjamans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pinjam', 30)->unique();
            $table->foreignId('buku_id')->constrained('bukus')->cascadeOnDelete();
            $table->string('nim_peminjam', 20);
            $table->string('nama_peminjam', 150);
            $table->date('tanggal_pinjam');
            $table->date('tanggal_jatuh_tempo');
            $table->date('tanggal_kembali')->nullable();
            $table->string('status', 20)->default('dipinjam'); // 'dipinjam' atau 'dikembalikan'
            $table->timestamps();

            // Index untuk optimasi kueri & verifikasi pinjaman aktif
            $table->index(['buku_id', 'nim_peminjam', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjamans');
    }
};
