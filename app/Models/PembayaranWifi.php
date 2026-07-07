<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PembayaranWifi extends Model
{
    protected $table = 'pembayaran_wifi';

    protected $fillable = [
        'pelanggan_id',
        'bulan_tagihan',
        'tahun_tagihan',
        'total_tagihan',
        'nominal_dibayar',
        'sisa_tagihan',
        'status',
    ];

    protected $casts = [
        'total_tagihan'   => 'decimal:2',
        'nominal_dibayar' => 'decimal:2',
        'sisa_tagihan'    => 'decimal:2',
        'bulan_tagihan'   => 'integer',
        'tahun_tagihan'   => 'integer',
    ];

    /**
     * Status yang valid
     */
    const STATUS_BELUM_DIBAYAR = 'Belum Dibayar';
    const STATUS_CICILAN       = 'Cicilan';
    const STATUS_LUNAS         = 'Lunas';

    /**
     * Nama-nama bulan dalam Bahasa Indonesia.
     */
    public static array $namaBulan = [
        1  => 'Januari',
        2  => 'Februari',
        3  => 'Maret',
        4  => 'April',
        5  => 'Mei',
        6  => 'Juni',
        7  => 'Juli',
        8  => 'Agustus',
        9  => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    /**
     * Mengembalikan nama bulan dalam Bahasa Indonesia.
     */
    public function getNamaBulanAttribute(): string
    {
        return self::$namaBulan[$this->bulan_tagihan] ?? '-';
    }

    /**
     * Warna badge sesuai status untuk Tailwind CSS.
     */
    public function getStatusColorAttribute(): array
    {
        return match ($this->status) {
            self::STATUS_LUNAS         => ['bg' => 'bg-emerald-500/15', 'text' => 'text-emerald-300', 'label' => '✓ Lunas'],
            self::STATUS_CICILAN       => ['bg' => 'bg-amber-500/15',   'text' => 'text-amber-300',   'label' => '◑ Cicilan'],
            self::STATUS_BELUM_DIBAYAR => ['bg' => 'bg-slate-500/15',   'text' => 'text-slate-300',   'label' => '○ Belum Dibayar'],
            default                    => ['bg' => 'bg-slate-500/15',   'text' => 'text-slate-400',   'label' => $this->status],
        };
    }

    /**
     * Scope: tagihan yang belum lunas (perlu dibayar).
     */
    public function scopeBelumLunas($query)
    {
        return $query->whereIn('status', [self::STATUS_BELUM_DIBAYAR, self::STATUS_CICILAN]);
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function cicilanPembayaran(): HasMany
    {
        return $this->hasMany(CicilanPembayaran::class, 'pembayaran_wifi_id');
    }
}
