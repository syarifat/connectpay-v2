<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaChatHistory extends Model
{
    protected $table = 'wa_chat_histories';

    protected $fillable = [
        'pelanggan_id',
        'target',
        'message',
        'status',
        'response',
    ];

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }
}
