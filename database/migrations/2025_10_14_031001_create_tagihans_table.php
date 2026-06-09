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
        Schema::create('tagihans', function (Blueprint $table) {
            $table->id();
            $table->string('no_tagihan')->unique();   // nomor invoice/tagihan unik
            $table->char('id_bulan', 2);              // relasi ke bulans.id_bulan (char[2])
            $table->year('tahun');                    // misal: 2025
            $table->string('id_pelanggan');           // relasi ke pelanggans.id_pelanggan (string)
            $table->decimal('jumlah_tagihan', 15, 2);
            $table->enum('status', ['belum', 'lunas'])->default('belum');
            $table->date('tgl_bayar')->nullable();

            // jika user dihapus, biarkan tagihan tetap ada (user_id -> null)
            $table->foreignId('user_id')->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('remark1')->nullable();
            $table->string('remark2')->nullable();
            $table->string('remark3')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_bulan')
                ->references('id_bulan')
                ->on('bulans');

            $table->foreign('id_pelanggan')
                ->references('id_pelanggan')
                ->on('pelanggans')
                ->cascadeOnDelete();

            // Constraints & Indexes
            $table->unique(['id_pelanggan', 'id_bulan', 'tahun'], 'uniq_pelanggan_bulan_tahun');
            $table->index(['tahun', 'id_bulan']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihans');
    }
};
