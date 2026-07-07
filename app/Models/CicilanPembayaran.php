<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CicilanPembayaran extends Model
{
    protected $table = 'cicilan_pembayaran';

    protected $fillable = [
        'pembayaran_wifi_id',
        'tanggal_bayar',
        'nominal',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'nominal'       => 'decimal:2',
    ];

    public function pembayaranWifi(): BelongsTo
    {
        return $this->belongsTo(PembayaranWifi::class, 'pembayaran_wifi_id');
    }
}
