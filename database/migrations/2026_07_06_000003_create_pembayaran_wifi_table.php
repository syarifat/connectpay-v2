<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_wifi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->restrictOnDelete();
            $table->tinyInteger('bulan_tagihan')->unsigned()->comment('1-12');
            $table->smallInteger('tahun_tagihan')->unsigned();
            $table->decimal('total_tagihan', 12, 2);
            $table->decimal('nominal_dibayar', 12, 2)->default(0);
            $table->decimal('sisa_tagihan', 12, 2)->default(0);
            $table->enum('status', ['Lunas', 'Cicilan'])->default('Cicilan');
            $table->timestamps();

            // Satu pelanggan hanya boleh punya satu tagihan per bulan/tahun
            $table->unique(['pelanggan_id', 'bulan_tagihan', 'tahun_tagihan'], 'unique_tagihan_per_bulan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_wifi');
    }
};
