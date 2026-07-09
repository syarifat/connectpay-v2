<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran_wifi', function (Blueprint $table) {
            // Menyimpan kapan terakhir kali reminder dikirim untuk tagihan ini
            $table->timestamp('reminder_sent_at')->nullable()->after('secure_key');
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran_wifi', function (Blueprint $table) {
            $table->dropColumn('reminder_sent_at');
        });
    }
};
