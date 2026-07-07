<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cicilan_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembayaran_wifi_id')->constrained('pembayaran_wifi')->cascadeOnDelete();
            $table->date('tanggal_bayar');
            $table->decimal('nominal', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cicilan_pembayaran');
    }
};
