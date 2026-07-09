<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pembayaran_wifi', function (Blueprint $table) {
            $table->string('secure_key', 20)->nullable()->unique()->after('id');
        });

        // Populate existing records
        $records = DB::table('pembayaran_wifi')->get();
        foreach ($records as $record) {
            do {
                $key = Str::random(20);
            } while (DB::table('pembayaran_wifi')->where('secure_key', $key)->exists());

            DB::table('pembayaran_wifi')->where('id', $record->id)->update([
                'secure_key' => $key
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran_wifi', function (Blueprint $table) {
            $table->dropColumn('secure_key');
        });
    }
};
