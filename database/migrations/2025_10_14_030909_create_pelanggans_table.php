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
        Schema::create('pelanggans', function (Blueprint $table) {
            $table->id();
            $table->string('id_pelanggan')->unique();
            $table->string('nama');
            $table->text('alamat')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('email')->nullable();
            $table->string('password');

            // Relasi
            $table->foreignId('id_paket')
                ->nullable()
                ->constrained('pakets')
                ->cascadeOnDelete();

            $table->foreignId('id_server')
                ->nullable()
                ->constrained('servers')
                ->cascadeOnDelete();

            // Info teknis
            $table->string('ip_router')->nullable();
            $table->string('ip_parent_router')->nullable();

            // Catatan tambahan
            $table->string('remark1')->nullable();
            $table->string('remark2')->nullable();
            $table->string('remark3')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggans');
    }
};
