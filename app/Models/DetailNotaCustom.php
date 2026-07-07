<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailNotaCustom extends Model
{
    protected $table = 'detail_nota_custom';

    protected $fillable = [
        'nota_custom_id',
        'nama_item',
        'kuantitas',
        'harga_satuan',
        'subtotal',
    ];

    protected $casts = [
        'kuantitas'    => 'integer',
        'harga_satuan' => 'decimal:2',
        'subtotal'     => 'decimal:2',
    ];

    public function notaCustom(): BelongsTo
    {
        return $this->belongsTo(NotaCustom::class, 'nota_custom_id');
    }
}
