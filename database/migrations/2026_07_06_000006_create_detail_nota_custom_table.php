<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_nota_custom', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nota_custom_id')->constrained('nota_custom')->cascadeOnDelete();
            $table->string('nama_item', 200);
            $table->integer('kuantitas')->unsigned()->default(1);
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_nota_custom');
    }
};
