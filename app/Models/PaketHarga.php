<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaketHarga extends Model
{
    protected $table = 'paket_harga';

    protected $fillable = [
        'nama_paket',
        'harga',
        'deskripsi',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
    ];

    public function pelanggan(): HasMany
    {
        return $this->hasMany(Pelanggan::class, 'paket_id');
    }
}
