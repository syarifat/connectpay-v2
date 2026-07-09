<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi Tagihan WiFi — {{ $tagihan->pelanggan->nama }} — {{ $tagihan->nama_bulan }} {{ $tagihan->tahun_tagihan }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }

        body { background: #f1f5f9; }

        .page {
            width: 148mm;
            min-height: 200mm;
            margin: 10mm auto;
            background: white;
            padding: 12mm;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        @media print {
            html, body { background: white; margin: 0; padding: 0; }
            .page {
                width: 100%;
                margin: 0;
                padding: 10mm;
                box-shadow: none;
            }
            .no-print { display: none !important; }
            @page { size: A5; margin: 5mm; }
        }

        .kop-border { border-bottom: 2px solid #1e40af; }
        .label { color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 500; }
        .value { color: #0f172a; font-size: 12px; font-weight: 600; }
        .divider { border-top: 1px dashed #cbd5e1; margin: 6px 0; }
        .total-row { background: #eff6ff; }
    </style>
</head>
<body>


{{-- ===== KUITANSI ===== --}}
<div class="page">

    {{-- KOP SURAT --}}
    <div class="flex items-start justify-between mb-4 pb-4 kop-border">
        <div class="flex items-center gap-3">
            {{-- Logo --}}
            <div class="w-12 h-12 rounded-xl bg-blue-700 flex items-center justify-center flex-shrink-0">
                <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z" />
                </svg>
            </div>
            <div>
                <h1 style="font-size:16px; font-weight:800; color:#1e3a8a; line-height:1.2;">ConnectPay</h1>
                <p style="font-size:10px; color:#475569;">Penyedia Layanan Internet</p>
            </div>
        </div>
        <div class="text-right" style="font-size:10px; color:#64748b; line-height:1.7;">
            <p>Dsn. Patik, Ds. Batangsaren</p>
            <p>Kec. Kauman, Kab. Tulungagung</p>
            <p>WhatsApp: +62878-5901-7087</p>
        </div>
    </div>

    {{-- JUDUL KUITANSI --}}
    <div class="text-center mb-5">
        <h2 style="font-size:14px; font-weight:800; letter-spacing:0.1em; color:#1e3a8a; text-transform:uppercase;">
            KUITANSI TAGIHAN INTERNET
        </h2>
        <div style="width:40px; height:3px; background:#3b82f6; margin:4px auto 0;"></div>
    </div>

    {{-- INFO TAGIHAN --}}
    <div class="grid gap-1 mb-4" style="grid-template-columns: 100px 8px 1fr; font-size:11px;">
        <span class="label">No. Tagihan</span>
        <span style="color:#94a3b8;">:</span>
        <span class="value">TWF-{{ str_pad($tagihan->id, 5, '0', STR_PAD_LEFT) }}</span>

        <span class="label">Tanggal Cetak</span>
        <span style="color:#94a3b8;">:</span>
        <span class="value">{{ now()->isoFormat('D MMMM Y') }}</span>

        <span class="label">Periode</span>
        <span style="color:#94a3b8;">:</span>
        <span class="value">{{ $tagihan->nama_bulan }} {{ $tagihan->tahun_tagihan }}</span>
    </div>

    <div class="divider"></div>

    {{-- INFO PELANGGAN --}}
    <p class="label mb-2">Data Pelanggan</p>
    <div class="grid gap-1 mb-4" style="grid-template-columns: 100px 8px 1fr; font-size:11px;">
        <span class="label">Nama</span>
        <span style="color:#94a3b8;">:</span>
        <span class="value">{{ $tagihan->pelanggan->nama }}</span>

        <span class="label">Alamat</span>
        <span style="color:#94a3b8;">:</span>
        <span class="value">{{ $tagihan->pelanggan->alamat }}</span>

        <span class="label">No. HP</span>
        <span style="color:#94a3b8;">:</span>
        <span class="value">{{ $tagihan->pelanggan->no_hp }}</span>

        <span class="label">Paket</span>
        <span style="color:#94a3b8;">:</span>
        <span class="value" style="color:#1d4ed8;">{{ $tagihan->pelanggan->paketHarga->nama_paket ?? '-' }}</span>
    </div>

    <div class="divider"></div>

    {{-- RINCIAN TAGIHAN --}}
    <p class="label mb-2">Rincian Tagihan</p>
    <table style="width:100%; font-size:11px; border-collapse:collapse;">
        <thead>
            <tr style="background:#eff6ff; border-bottom:1px solid #bfdbfe;">
                <th style="padding:5px 8px; text-align:left; color:#1e40af; font-weight:600; font-size:10px; text-transform:uppercase; letter-spacing:0.05em;">Keterangan</th>
                <th style="padding:5px 8px; text-align:right; color:#1e40af; font-weight:600; font-size:10px; text-transform:uppercase; letter-spacing:0.05em;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            {{-- Tunggakan bulan-bulan sebelumnya (jika ada) --}}
            @foreach($tunggakanLain as $tgl)
            <tr style="border-bottom:1px solid #fef2f2; background:#fffbeb;">
                <td style="padding:6px 8px;">
                    <div style="color:#92400e; font-weight:600;">
                        ⚠ Tunggakan — {{ $tgl->nama_bulan }} {{ $tgl->tahun_tagihan }}
                    </div>
                    <div style="color:#94a3b8; font-size:10px; margin-top:1px;">
                        Total: Rp {{ number_format($tgl->total_tagihan, 0, ',', '.') }}
                        &nbsp;|&nbsp; Dibayar: Rp {{ number_format($tgl->nominal_dibayar, 0, ',', '.') }}
                        &nbsp;|&nbsp; Status: {{ $tgl->status }}
                    </div>
                </td>
                <td style="padding:6px 8px; text-align:right; color:#dc2626; font-weight:700;">
                    Rp {{ number_format($tgl->sisa_tagihan, 0, ',', '.') }}
                </td>
            </tr>
            @endforeach

            {{-- Tagihan bulan ini --}}
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:6px 8px; color:#334155;">
                    Tagihan Internet — Bulan {{ $tagihan->nama_bulan }} {{ $tagihan->tahun_tagihan }}
                    <br><span style="color:#94a3b8; font-size:10px;">Paket: {{ $tagihan->pelanggan->paketHarga->nama_paket ?? '-' }}</span>
                </td>
                <td style="padding:6px 8px; text-align:right; color:#0f172a; font-weight:600;">
                    Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- HISTORI CICILAN tagihan bulan ini (jika sudah ada pembayaran sebagian) --}}
    @if($tagihan->cicilanPembayaran->count() > 0)
    <div class="mt-3">
        <p class="label mb-1" style="font-size:9px;">Riwayat Pembayaran Bulan {{ $tagihan->nama_bulan }}</p>
        <table style="width:100%; font-size:10px; border-collapse:collapse;">
            <tbody>
                @foreach($tagihan->cicilanPembayaran as $cicilan)
                <tr style="border-bottom:1px solid #f8fafc;">
                    <td style="padding:3px 8px; color:#475569;">{{ $cicilan->tanggal_bayar->isoFormat('D MMM Y') }}</td>
                    <td style="padding:3px 8px; text-align:right; color:#059669; font-weight:600;">
                        − Rp {{ number_format($cicilan->nominal, 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- TOTAL SUMMARY --}}
    @php
        $totalTunggakan = $tunggakanLain->sum('sisa_tagihan');
        $totalKeseluruhan = $totalTunggakan + $tagihan->sisa_tagihan;
    @endphp

    <div class="mt-4" style="border:1px solid #bfdbfe; border-radius:8px; overflow:hidden;">
        {{-- Baris tunggakan jika ada --}}
        @if($tunggakanLain->count() > 0)
        <div style="padding:6px 10px; background:#fffbeb; border-bottom:1px solid #fde68a; display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:10px; color:#92400e; font-weight:600;">⚠ Total Tunggakan Bulan Sebelumnya ({{ $tunggakanLain->count() }} tagihan)</span>
            <span style="font-size:11px; font-weight:700; color:#dc2626;">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</span>
        </div>
        <div style="padding:6px 10px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:10px; color:#64748b; font-weight:500;">Tagihan Bulan {{ $tagihan->nama_bulan }} {{ $tagihan->tahun_tagihan }}</span>
            <span style="font-size:11px; font-weight:600; color:#0f172a;">Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}</span>
        </div>
        <div style="padding:8px 10px; background:#eff6ff; display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:11px; font-weight:800; color:#1e40af; text-transform:uppercase; letter-spacing:0.05em;">TOTAL YANG HARUS DIBAYAR</span>
            <span style="font-size:14px; font-weight:800; color:#1e40af;">Rp {{ number_format($totalKeseluruhan, 0, ',', '.') }}</span>
        </div>
        @else
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr;">
            <div style="padding:8px 10px; background:#f8fafc; border-right:1px solid #e2e8f0; text-align:center;">
                <p style="font-size:9px; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; font-weight:500; margin-bottom:2px;">Total Tagihan</p>
                <p style="font-size:12px; font-weight:700; color:#0f172a;">Rp {{ number_format($tagihan->total_tagihan, 0, ',', '.') }}</p>
            </div>
            <div style="padding:8px 10px; background:#f0fdf4; border-right:1px solid #e2e8f0; text-align:center;">
                <p style="font-size:9px; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; font-weight:500; margin-bottom:2px;">Sudah Dibayar</p>
                <p style="font-size:12px; font-weight:700; color:#059669;">Rp {{ number_format($tagihan->nominal_dibayar, 0, ',', '.') }}</p>
            </div>
            <div style="padding:8px 10px; background:{{ $tagihan->sisa_tagihan > 0 ? '#fef2f2' : '#f0fdf4' }}; text-align:center;">
                <p style="font-size:9px; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; font-weight:500; margin-bottom:2px;">Sisa Tagihan</p>
                <p style="font-size:12px; font-weight:700; color:{{ $tagihan->sisa_tagihan > 0 ? '#dc2626' : '#059669' }};">
                    Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}
                </p>
            </div>
        </div>
        @endif
    </div>

    {{-- STATUS BADGE --}}
    <div class="text-center mt-3">
        @if($tagihan->status === 'Lunas')
        <span style="display:inline-block; padding:4px 16px; background:#dcfce7; color:#166534; border:1px solid #86efac;
                     border-radius:20px; font-size:12px; font-weight:700; letter-spacing:0.05em;">
            ✓ LUNAS
        </span>
        @else
        <span style="display:inline-block; padding:4px 16px; background:#fef9c3; color:#854d0e; border:1px solid #fde047;
                     border-radius:20px; font-size:12px; font-weight:700; letter-spacing:0.05em;">
            ◑ BELUM LUNAS
        </span>
        @endif
    </div>

    {{-- Footer --}}
    <p style="text-align:center; font-size:9px; color:#94a3b8; margin-top:12px; border-top:1px solid #f1f5f9; padding-top:6px;">
        Terima kasih telah menggunakan layanan ConnectPay Internet. Dokumen ini dicetak pada {{ now()->isoFormat('D MMMM Y, HH:mm') }}.
    </p>

</div>{{-- /page --}}

</body>
</html>
