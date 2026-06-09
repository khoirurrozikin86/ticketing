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
        Schema::create('bulans', function (Blueprint $table) {
            $table->id(); // id auto increment
            $table->char('id_bulan', 2)->unique(); // contoh: '01', '02', dst
            $table->string('bulan', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulans');
    }
};
