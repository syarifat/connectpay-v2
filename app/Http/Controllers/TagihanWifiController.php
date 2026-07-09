<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\PembayaranWifi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagihanWifiController extends Controller
{
    /**
     * Daftar semua tagihan WiFi.
     */
    public function index(Request $request): View
    {
        $query = PembayaranWifi::with(['pelanggan.paketHarga'])->latest();

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter pelanggan
        if ($request->filled('pelanggan_id')) {
            $query->where('pelanggan_id', $request->pelanggan_id);
        }

        $tagihan   = $query->paginate(15)->withQueryString();
        $pelanggan = Pelanggan::orderBy('nama')->get();

        return view('tagihan-wifi.index', compact('tagihan', 'pelanggan'));
    }

    /**
     * Form buat tagihan baru.
     */
    public function create(): View
    {
        $pelanggan = Pelanggan::with('paketHarga')->orderBy('nama')->get();
        $tahunList = range(date('Y'), date('Y') - 2);

        return view('tagihan-wifi.create', compact('pelanggan', 'tahunList'));
    }

    /**
     * Simpan tagihan baru (status awal: Belum Dibayar).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pelanggan_id'  => 'required|exists:pelanggan,id',
            'bulan_tagihan' => 'required|integer|between:1,12',
            'tahun_tagihan' => 'required|integer|min:2000',
        ]);

        $pelanggan    = Pelanggan::with('paketHarga')->findOrFail($validated['pelanggan_id']);
        $totalTagihan = $pelanggan->paketHarga->harga;

        // Cek apakah tagihan bulan ini sudah ada
        $sudahAda = PembayaranWifi::where('pelanggan_id', $validated['pelanggan_id'])
            ->where('bulan_tagihan', $validated['bulan_tagihan'])
            ->where('tahun_tagihan', $validated['tahun_tagihan'])
            ->exists();

        if ($sudahAda) {
            $namaBulan = PembayaranWifi::$namaBulan[$validated['bulan_tagihan']];

            return redirect()->route('tagihan-wifi.create')
                ->with('error', "Tagihan bulan {$namaBulan} {$validated['tahun_tagihan']} untuk pelanggan {$pelanggan->nama} sudah ada.")
                ->withInput();
        }

        // Buat tagihan baru dengan status Belum Dibayar
        PembayaranWifi::create([
            'pelanggan_id'    => $validated['pelanggan_id'],
            'bulan_tagihan'   => $validated['bulan_tagihan'],
            'tahun_tagihan'   => $validated['tahun_tagihan'],
            'total_tagihan'   => $totalTagihan,
            'nominal_dibayar' => 0,
            'sisa_tagihan'    => $totalTagihan,
            'status'          => PembayaranWifi::STATUS_BELUM_DIBAYAR,
        ]);

        $namaBulan = PembayaranWifi::$namaBulan[$validated['bulan_tagihan']];

        return redirect()->route('tagihan-wifi.index')
            ->with('success', "Tagihan {$namaBulan} {$validated['tahun_tagihan']} untuk {$pelanggan->nama} berhasil dibuat.");
    }

    /**
     * Halaman cetak kuitansi.
     * Selain tagihan utama, juga tampilkan tunggakan bulan-bulan sebelumnya
     * milik pelanggan yang sama (status Belum Dibayar / Cicilan).
     */
    public function cetak(int $id): View
    {
        $tagihan = PembayaranWifi::with([
            'pelanggan.paketHarga',
            'cicilanPembayaran',
        ])->findOrFail($id);

        // Tagihan lain (bulan berbeda) milik pelanggan yang sama yang belum lunas,
        // urut dari yang terlama → paling baru, kecuali tagihan saat ini
        $tunggakanLain = PembayaranWifi::where('pelanggan_id', $tagihan->pelanggan_id)
            ->where('id', '!=', $tagihan->id)
            ->belumLunas()
            ->orderBy('tahun_tagihan')
            ->orderBy('bulan_tagihan')
            ->get();

        return view('tagihan-wifi.cetak', compact('tagihan', 'tunggakanLain'));
    }

    /**
     * API: Ambil info cicilan pelanggan bulan sebelumnya (untuk warning di form).
     * Dipakai via fetch() Alpine.js di form create tagihan.
     */
    public function cekCicilanLalu(int $pelangganId): \Illuminate\Http\JsonResponse
    {
        $cicilanBelumLunas = PembayaranWifi::where('pelanggan_id', $pelangganId)
            ->belumLunas()
            ->orderBy('tahun_tagihan')
            ->orderBy('bulan_tagihan')
            ->get(['id', 'bulan_tagihan', 'tahun_tagihan', 'sisa_tagihan', 'status']);

        $result = $cicilanBelumLunas->map(fn ($t) => [
            'id'            => $t->id,
            'nama_bulan'    => $t->nama_bulan,
            'tahun_tagihan' => $t->tahun_tagihan,
            'sisa_tagihan'  => $t->sisa_tagihan,
            'sisa_format'   => 'Rp ' . number_format($t->sisa_tagihan, 0, ',', '.'),
            'status'        => $t->status,
        ]);

        return response()->json($result);
    }

    /**
     * Generate dynamic billing invoice as PNG image.
     */
    public function generateImage(int $id)
    {
        try {
            if (!extension_loaded('gd')) {
                throw new \Exception("Ekstensi PHP 'gd' tidak diaktifkan di server produksi Anda. Silakan aktifkan ekstensi 'gd' di konfigurasi PHP/cPanel server Anda agar dapat menghasilkan gambar tagihan.");
            }

            $tagihan = PembayaranWifi::with([
                'pelanggan.paketHarga',
                'cicilanPembayaran',
            ])->findOrFail($id);

            $tunggakanLain = PembayaranWifi::where('pelanggan_id', $tagihan->pelanggan_id)
                ->where('id', '!=', $tagihan->id)
                ->belumLunas()
                ->get();

            $totalTunggakan = $tunggakanLain->sum('sisa_tagihan');
            $grandTotal = $tagihan->sisa_tagihan + $totalTunggakan;

            // Create image: 600 x 850
            $width = 600;
            $height = 850;
            $im = imagecreatetruecolor($width, $height);

            // Define colors
            $white = imagecolorallocate($im, 255, 255, 255);
            $bg = imagecolorallocate($im, 248, 250, 252); // Slate light background (f8fafc)
            $primary = imagecolorallocate($im, 37, 99, 235); // Primary Blue (2563eb)
            $textDark = imagecolorallocate($im, 15, 23, 42); // Slate dark text (0f172a)
            $textMuted = imagecolorallocate($im, 100, 116, 139); // Slate secondary text (64748b)
            $border = imagecolorallocate($im, 226, 232, 240); // Slate border (e2e8f0)
            $green = imagecolorallocate($im, 5, 150, 105); // Emerald Green (059669)
            $greenBg = imagecolorallocate($im, 209, 250, 229); // Light green background (d1fae5)
            $red = imagecolorallocate($im, 220, 38, 38); // Alert Red (dc2626)
            $redBg = imagecolorallocate($im, 254, 226, 226); // Light red background (fee2e2)

            // Fill background
            imagefill($im, 0, 0, $bg);

            // Draw card background
            imagefilledrectangle($im, 20, 20, 580, 830, $white);
            imagerectangle($im, 20, 20, 580, 830, $border);

            // Header block
            imagefilledrectangle($im, 21, 21, 579, 120, $primary);

            // Text rendering helper (Multi-OS support: macOS & Linux)
            $fontPaths = [
                '/System/Library/Fonts/Supplemental/Arial.ttf',
                '/System/Library/Fonts/Arial.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
                '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
                '/usr/share/fonts/truetype/msttcorefonts/Arial.ttf',
            ];
            
            $fontBoldPaths = [
                '/System/Library/Fonts/Supplemental/Arial Bold.ttf',
                '/System/Library/Fonts/Arial Bold.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
                '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
                '/usr/share/fonts/truetype/msttcorefonts/Arial_Bold.ttf',
            ];

            $fontPath = null;
            foreach ($fontPaths as $path) {
                if (file_exists($path)) {
                    $fontPath = $path;
                    break;
                }
            }

            $fontBoldPath = null;
            foreach ($fontBoldPaths as $path) {
                if (file_exists($path)) {
                    $fontBoldPath = $path;
                    break;
                }
            }

            if ($fontPath !== null && $fontBoldPath !== null) {
                // Title
                imagettftext($im, 20, 0, 40, 65, $white, $fontBoldPath, "CONNECTPAY INVOICE");
                imagettftext($im, 11, 0, 40, 95, $white, $fontPath, "Layanan Tagihan Internet WiFi");

                // Invoice Info
                imagettftext($im, 11, 0, 40, 160, $textMuted, $fontPath, "NO. NOTA TAGIHAN");
                imagettftext($im, 13, 0, 40, 185, $textDark, $fontBoldPath, "INV/WiFi/" . $tagihan->tahun_tagihan . "/" . str_pad($tagihan->id, 5, '0', STR_PAD_LEFT));

                imagettftext($im, 11, 0, 330, 160, $textMuted, $fontPath, "TANGGAL JATUH TEMPO");
                imagettftext($im, 13, 0, 330, 185, $textDark, $fontBoldPath, ($tagihan->pelanggan->tanggal_pembayaran ?? 10) . " " . PembayaranWifi::$namaBulan[$tagihan->bulan_tagihan] . " " . $tagihan->tahun_tagihan);

                // Divider
                imageline($im, 40, 210, 560, 210, $border);

                // Pelanggan Info
                imagettftext($im, 11, 0, 40, 240, $textMuted, $fontPath, "DITAGIHKAN KEPADA");
                imagettftext($im, 14, 0, 40, 265, $textDark, $fontBoldPath, $tagihan->pelanggan->nama);
                imagettftext($im, 11, 0, 40, 290, $textMuted, $fontPath, "No. WA: " . $tagihan->pelanggan->no_hp);
                imagettftext($im, 11, 0, 40, 312, $textMuted, $fontPath, "Alamat: " . $tagihan->pelanggan->alamat);

                // Divider
                imageline($im, 40, 340, 560, 340, $border);

                // Rincian Tagihan
                imagettftext($im, 12, 0, 40, 375, $textDark, $fontBoldPath, "DESKRIPSI LAYANAN");
                imagettftext($im, 12, 0, 450, 375, $textDark, $fontBoldPath, "JUMLAH");

                // Item 1: Paket internet
                $paketName = $tagihan->pelanggan->paketHarga->nama_paket ?? "Paket Internet";
                imagettftext($im, 11, 0, 40, 415, $textDark, $fontPath, "Biaya Berlangganan: " . $paketName);
                imagettftext($im, 11, 0, 40, 435, $textMuted, $fontPath, "Periode " . PembayaranWifi::$namaBulan[$tagihan->bulan_tagihan] . " " . $tagihan->tahun_tagihan);
                imagettftext($im, 12, 0, 450, 425, $textDark, $fontBoldPath, "Rp " . number_format($tagihan->total_tagihan, 0, ',', '.'));

                // Divider
                imageline($im, 40, 465, 560, 465, $border);

                // Tunggakan rincian
                $currentY = 500;
                if ($totalTunggakan > 0) {
                    imagettftext($im, 11, 0, 40, $currentY, $textDark, $fontPath, "Tunggakan Bulan Sebelumnya:");
                    $currentY += 25;
                    foreach ($tunggakanLain as $t) {
                        imagettftext($im, 10, 0, 60, $currentY, $red, $fontPath, "- Periode " . PembayaranWifi::$namaBulan[$t->bulan_tagihan] . " " . $t->tahun_tagihan);
                        imagettftext($im, 11, 0, 450, $currentY, $red, $fontBoldPath, "Rp " . number_format($t->sisa_tagihan, 0, ',', '.'));
                        $currentY += 25;
                    }
                    imageline($im, 40, $currentY, 560, $currentY, $border);
                    $currentY += 35;
                }

                // Total Keseluruhan
                imagettftext($im, 14, 0, 40, $currentY, $textDark, $fontBoldPath, "TOTAL PEMBAYARAN");
                imagettftext($im, 18, 0, 420, $currentY + 5, $primary, $fontBoldPath, "Rp " . number_format($grandTotal, 0, ',', '.'));

                // Status Stamp
                $statusY = $currentY + 60;
                if ($tagihan->status === PembayaranWifi::STATUS_LUNAS) {
                    imagefilledrectangle($im, 40, $statusY, 200, $statusY + 45, $greenBg);
                    imagerectangle($im, 40, $statusY, 200, $statusY + 45, $green);
                    imagettftext($im, 13, 0, 85, $statusY + 28, $green, $fontBoldPath, "LUNAS");
                } else {
                    imagefilledrectangle($im, 40, $statusY, 240, $statusY + 45, $redBg);
                    imagerectangle($im, 40, $statusY, 240, $statusY + 45, $red);
                    imagettftext($im, 12, 0, 70, $statusY + 28, $red, $fontBoldPath, "BELUM DIBAYAR");
                }

                // Footer note
                imagettftext($im, 9, 0, 40, 800, $textMuted, $fontPath, "* Mohon lakukan pembayaran tepat waktu agar kenyamanan internet tetap terjaga.");
                imagettftext($im, 9, 0, 40, 815, $textMuted, $fontPath, "* Hubungi Admin jika Anda memerlukan bantuan pembayaran.");

            } else {
                // Fallback to built-in GD fonts if TTF fonts are not found
                imagestring($im, 5, 40, 40, "CONNECTPAY INVOICE", $primary);
                imageline($im, 40, 70, 560, 70, $border);
                imagestring($im, 4, 40, 90, "Invoice: INV/WiFi/" . $tagihan->tahun_tagihan . "/" . $tagihan->id, $textDark);
                imagestring($im, 4, 40, 115, "Customer: " . $tagihan->pelanggan->nama, $textDark);
                imagestring($im, 4, 40, 140, "Total: Rp " . number_format($grandTotal, 0, ',', '.'), $textDark);
                imagestring($im, 4, 40, 165, "Status: " . strtoupper($tagihan->status), $red);
            }

            // Output image
            ob_start();
            imagepng($im);
            $contents = ob_get_clean();
            imagedestroy($im);

            return response($contents, 200)
                ->header('Content-Type', 'image/png');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
