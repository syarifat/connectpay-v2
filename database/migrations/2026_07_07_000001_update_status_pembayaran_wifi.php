<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL tidak support modifikasi ENUM langsung via Blueprint dengan mudah,
        // gunakan raw statement untuk update kolom enum
        DB::statement("ALTER TABLE pembayaran_wifi MODIFY COLUMN status ENUM('Belum Dibayar', 'Cicilan', 'Lunas') NOT NULL DEFAULT 'Belum Dibayar'");
    }

    public function down(): void
    {
        // Kembalikan ke enum lama
        DB::statement("ALTER TABLE pembayaran_wifi MODIFY COLUMN status ENUM('Lunas', 'Cicilan') NOT NULL DEFAULT 'Cicilan'");
    }
};
