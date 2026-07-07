<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota {{ $nota->nomor_nota }} — {{ $nota->nama_pembeli }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; box-sizing: border-box; }

        body { background: #f1f5f9; margin: 0; padding: 0; }

        .page {
            width: 210mm;
            min-height: 148mm;
            margin: 10mm auto;
            background: white;
            padding: 14mm;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        @media print {
            html, body { background: white; margin: 0; padding: 0; }
            .page {
                width: 100%;
                margin: 0;
                padding: 12mm;
                box-shadow: none;
                min-height: auto;
            }
            .no-print { display: none !important; }
            @page { size: A4 landscape; margin: 5mm; }
        }

        .kop-line { border-bottom: 3px double #1e40af; padding-bottom: 8px; margin-bottom: 10px; }
        th { background: #1e3a8a; color: white; padding: 7px 10px; font-size: 11px; text-align: left; font-weight: 600; }
        th:last-child { text-align: right; }
        th.center { text-align: center; }
        th.right { text-align: right; }
        td { padding: 6px 10px; font-size: 11px; color: #1e293b; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        td.center { text-align: center; }
        td.right { text-align: right; }
        tr:nth-child(even) td { background: #f8fafc; }
        .grand-total-row td { background: #eff6ff !important; font-weight: 700; font-size: 13px; border-top: 2px solid #bfdbfe; }
    </style>
</head>
<body>

{{-- Tombol aksi (tidak tercetak) --}}
<div class="no-print fixed top-4 right-4 flex gap-2 z-50">
    <button onclick="window.print()"
            class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl shadow-lg transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
        </svg>
        Cetak Nota
    </button>
    <a href="{{ route('nota-custom.index') }}"
       class="flex items-center gap-2 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm font-medium rounded-xl shadow transition-colors">
        ← Kembali
    </a>
</div>

{{-- ===== NOTA ===== --}}
<div class="page">

    {{-- KOP SURAT --}}
    <div class="flex items-start justify-between kop-line">
        <div class="flex items-center gap-3">
            {{-- Logo --}}
            <div style="width:52px; height:52px; background:linear-gradient(135deg,#1d4ed8,#1e40af); border-radius:12px;
                        display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg style="width:30px; height:30px; color:white;" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z" />
                </svg>
            </div>
            <div>
                <h1 style="font-size:20px; font-weight:800; color:#1e3a8a; margin:0; line-height:1.1;">ConnectPay</h1>
                <p style="font-size:10px; color:#64748b; margin:2px 0 0;">Penyedia Layanan Internet & Perdagangan</p>
            </div>
        </div>
        <div style="text-align:right; font-size:10px; color:#64748b; line-height:1.8;">
            <p style="margin:0;">Jl. Contoh No. 123, Kota Anda 12345</p>
            <p style="margin:0;">Telp: 0812-3456-7890 | WA: 0812-3456-7890</p>
            <p style="margin:0;">Email: admin@connectpay.id</p>
        </div>
    </div>

    {{-- JUDUL + META NOTA --}}
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
        <div>
            <h2 style="font-size:16px; font-weight:800; color:#1e3a8a; text-transform:uppercase; letter-spacing:0.08em; margin:0 0 4px;">
                NOTA PENJUALAN
            </h2>
            <div style="width:40px; height:3px; background:#3b82f6; border-radius:2px;"></div>
        </div>
        <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:8px 14px; text-align:right;">
            <p style="font-size:9px; color:#3b82f6; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin:0 0 2px;">Nomor Nota</p>
            <p style="font-size:14px; font-weight:800; color:#1e3a8a; font-family:monospace; margin:0;">{{ $nota->nomor_nota }}</p>
        </div>
    </div>

    {{-- INFO TRANSAKSI --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:12px;
                background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:10px 14px;">
        <div>
            <p style="font-size:9px; color:#94a3b8; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin:0 0 2px;">Kepada / Nama Pembeli</p>
            <p style="font-size:13px; font-weight:700; color:#0f172a; margin:0;">{{ $nota->nama_pembeli }}</p>
        </div>
        <div style="text-align:right;">
            <p style="font-size:9px; color:#94a3b8; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin:0 0 2px;">Tanggal Transaksi</p>
            <p style="font-size:13px; font-weight:700; color:#0f172a; margin:0;">{{ $nota->tanggal->isoFormat('D MMMM Y') }}</p>
        </div>
    </div>

    {{-- TABEL ITEM --}}
    <table style="width:100%; border-collapse:collapse; border-radius:8px; overflow:hidden;">
        <thead>
            <tr>
                <th style="width:32px; text-align:center;">No</th>
                <th>Nama Barang / Jasa</th>
                <th class="center" style="width:70px;">Qty</th>
                <th class="right" style="width:130px;">Harga Satuan</th>
                <th class="right" style="width:130px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($nota->detailNota as $idx => $detail)
            <tr>
                <td class="center" style="color:#94a3b8; font-weight:600;">{{ $idx + 1 }}</td>
                <td>{{ $detail->nama_item }}</td>
                <td class="center" style="font-weight:600;">{{ $detail->kuantitas }}</td>
                <td class="right">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                <td class="right" style="font-weight:600; color:#059669;">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach

            {{-- Subtotal row --}}
            @if($nota->detailNota->count() > 1)
            <tr>
                <td colspan="4" style="text-align:right; color:#64748b; font-weight:500; padding:5px 10px; border-top:1px dashed #e2e8f0;">
                    Subtotal ({{ $nota->detailNota->count() }} item)
                </td>
                <td class="right" style="font-weight:600; border-top:1px dashed #e2e8f0;">
                    Rp {{ number_format($nota->total_harga, 0, ',', '.') }}
                </td>
            </tr>
            @endif

            {{-- Grand Total --}}
            <tr class="grand-total-row">
                <td colspan="4" style="text-align:right; color:#1e40af; letter-spacing:0.05em; text-transform:uppercase; font-size:12px;">
                    TOTAL PEMBAYARAN
                </td>
                <td class="right" style="color:#1e40af; font-size:14px;">
                    Rp {{ number_format($nota->total_harga, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Terbilang --}}
    {{--
        Opsional: terbilang bisa ditambahkan jika ada helper/package.
        Contoh: <p>Terbilang: <em>Seratus lima puluh ribu rupiah</em></p>
    --}}

    {{-- FOOTER + CATATAN --}}
    <div style="margin-top:16px; padding-top:10px; border-top:1px dashed #cbd5e1;">
        <p style="font-size:10px; color:#64748b; margin:0;">
            <span style="font-weight:500; color:#475569;">Catatan:</span>
            Barang yang sudah dibeli tidak dapat dikembalikan. Terima kasih atas kepercayaan Anda.
        </p>
    </div>

    {{-- Footer nota --}}
    <p style="text-align:center; font-size:9px; color:#94a3b8; margin-top:10px; padding-top:6px; border-top:1px solid #f1f5f9;">
        ConnectPay — Jl. Contoh No. 123 | Telp. 0812-3456-7890 | Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }}
    </p>

</div>{{-- /page --}}

</body>
</html>
