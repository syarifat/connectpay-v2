<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nota_custom', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_nota', 30)->unique();
            $table->date('tanggal');
            $table->string('nama_pembeli', 150);
            $table->decimal('total_harga', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nota_custom');
    }
};
