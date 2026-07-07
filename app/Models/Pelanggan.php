<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';

    protected $fillable = [
        'nama',
        'alamat',
        'no_hp',
        'paket_id',
    ];

    public function paketHarga(): BelongsTo
    {
        return $this->belongsTo(PaketHarga::class, 'paket_id');
    }

    public function pembayaranWifi(): HasMany
    {
        return $this->hasMany(PembayaranWifi::class, 'pelanggan_id');
    }
}
