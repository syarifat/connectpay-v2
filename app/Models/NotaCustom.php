<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotaCustom extends Model
{
    protected $table = 'nota_custom';

    protected $fillable = [
        'nomor_nota',
        'tanggal',
        'nama_pembeli',
        'total_harga',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'total_harga' => 'decimal:2',
    ];

    public function detailNota(): HasMany
    {
        return $this->hasMany(DetailNotaCustom::class, 'nota_custom_id');
    }
}
