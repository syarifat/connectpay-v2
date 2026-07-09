<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:send-billing-reminders')]
#[Description('Buat tagihan otomatis & kirim reminder WhatsApp ke pelanggan yang tanggal penagihannya hari ini')]
class SendBillingReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $todayDay = (int) date('j');
        $bulan    = (int) date('n');
        $tahun    = (int) date('Y');
        $today    = date('Y-m-d');

        $this->info("Memproses tagihan WhatsApp untuk tanggal penagihan hari ke-{$todayDay}...");

        $customers = \App\Models\Pelanggan::with('paketHarga')
            ->where('tanggal_pembayaran', $todayDay)
            ->get();

        if ($customers->isEmpty()) {
            $this->info("Tidak ada pelanggan dengan tanggal penagihan hari ini.");
            return 0;
        }

        $fonnte       = new \App\Services\FonnteService();
        $countSuccess = 0;
        $countFailed  = 0;
        $countSkipped = 0;

        foreach ($customers as $customer) {
            // 1. Cari atau buat tagihan periode berjalan secara otomatis
            $tagihan = \App\Models\PembayaranWifi::where('pelanggan_id', $customer->id)
                ->where('bulan_tagihan', $bulan)
                ->where('tahun_tagihan', $tahun)
                ->first();

            if (!$tagihan) {
                $totalTagihan = $customer->paketHarga->harga ?? 0.0;
                $tagihan = \App\Models\PembayaranWifi::create([
                    'pelanggan_id'    => $customer->id,
                    'bulan_tagihan'   => $bulan,
                    'tahun_tagihan'   => $tahun,
                    'total_tagihan'   => $totalTagihan,
                    'nominal_dibayar' => 0.0,
                    'sisa_tagihan'    => $totalTagihan,
                    'status'          => \App\Models\PembayaranWifi::STATUS_BELUM_DIBAYAR,
                ]);
                $this->info("Tagihan baru dibuat untuk {$customer->nama} — " . \App\Models\PembayaranWifi::$namaBulan[$bulan] . " {$tahun}.");
            }

            // Jika tagihan sudah lunas, lewatkan — tidak perlu mengirim reminder
            if ($tagihan->status === \App\Models\PembayaranWifi::STATUS_LUNAS) {
                $this->line("  → Lewati {$customer->nama}: tagihan sudah Lunas.");
                $countSkipped++;
                continue;
            }

            // 2. PROTEKSI DUPLIKAT: Cek apakah reminder sudah dikirim hari ini untuk tagihan ini
            if ($tagihan->reminder_sent_at && $tagihan->reminder_sent_at->format('Y-m-d') === $today) {
                $this->line("  → Lewati {$customer->nama}: reminder sudah dikirim hari ini pukul {$tagihan->reminder_sent_at->format('H:i')}.");
                $countSkipped++;
                continue;
            }

            // 3. Hitung tunggakan dari bulan-bulan sebelumnya
            $tunggakanLain = \App\Models\PembayaranWifi::where('pelanggan_id', $customer->id)
                ->where('id', '!=', $tagihan->id)
                ->belumLunas()
                ->get();

            $totalTunggakan = $tunggakanLain->sum('sisa_tagihan');
            $grandTotal     = $tagihan->sisa_tagihan + $totalTunggakan;

            // 4. Format pesan WhatsApp
            $namaBulan = \App\Models\PembayaranWifi::$namaBulan[$bulan];

            $message  = "*[REMINDER TAGIHAN CONNECTPAY]*\n\n";
            $message .= "Halo Kak *{$customer->nama}*,\n";
            $message .= "Tagihan WiFi ConnectPay Anda untuk periode *{$namaBulan} {$tahun}* sebesar *Rp " . number_format($tagihan->total_tagihan, 0, ',', '.') . "* telah diterbitkan.\n\n";

            if ($totalTunggakan > 0) {
                $message .= "⚠️ *Rincian Tunggakan Sebelumnya*:\n";
                foreach ($tunggakanLain as $t) {
                    $message .= "- Periode " . \App\Models\PembayaranWifi::$namaBulan[$t->bulan_tagihan] . " {$t->tahun_tagihan}: Rp " . number_format($t->sisa_tagihan, 0, ',', '.') . "\n";
                }
                $message .= "• Total Tunggakan: Rp " . number_format($totalTunggakan, 0, ',', '.') . "\n\n";
                $message .= "💵 *TOTAL KESELURUHAN*: *Rp " . number_format($grandTotal, 0, ',', '.') . "*\n\n";
            } else {
                $message .= "💵 *TOTAL HARUS DIBAYAR*: *Rp " . number_format($tagihan->sisa_tagihan, 0, ',', '.') . "*\n\n";
            }

            // Generate link kuitansi cetak
            $invoiceUrl = route('tagihan-wifi.cetak', $tagihan->secure_key);
            $baseUrl    = "https://connectpay.satcloud.tech";
            $invoiceUrl = str_replace(url('/'), $baseUrl, $invoiceUrl);

            $message .= "Rincian nota tagihan & kuitansi Anda dapat dilihat pada link berikut:\n{$invoiceUrl}\n\n";
            $message .= "Silakan lakukan pembayaran agar layanan internet tetap berjalan lancar. Terima kasih banyak.";

            // Generate invoice PNG lokal untuk dikirim langsung ke Fonnte (bypass Cloudflare)
            $filePath = null;
            try {
                $controller = new \App\Http\Controllers\TagihanWifiController();
                $response   = $controller->generateImage($tagihan->secure_key);
                if ($response->getStatusCode() === 200) {
                    $imageBinary = $response->getContent();
                    $filePath    = tempnam(sys_get_temp_dir(), 'inv_') . '.png';
                    file_put_contents($filePath, $imageBinary);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Gagal membuat file gambar tagihan sementara: " . $e->getMessage());
            }

            // 5. Ubah nomor HP ke format internasional
            $target = $customer->no_hp;
            if (str_starts_with($target, '0')) {
                $target = '62' . substr($target, 1);
            }

            // 6. Kirim via Fonnte dengan file gambar langsung
            $result = $fonnte->sendMessage($target, $message, null, "invoice_{$tagihan->id}.png", $filePath);

            // Bersihkan file sementara
            if ($filePath && file_exists($filePath)) {
                unlink($filePath);
            }

            // 7. Catat riwayat chat ke database
            \App\Models\WaChatHistory::create([
                'pelanggan_id' => $customer->id,
                'target'       => $customer->no_hp,
                'message'      => $message,
                'status'       => $result['success'] ? 'sent' : 'failed',
                'response'     => json_encode($result['raw'] ?? ['error' => $result['message']]),
            ]);

            if ($result['success']) {
                // Tandai waktu pengiriman agar endpoint tidak mengirim ulang di hari yang sama
                $tagihan->update(['reminder_sent_at' => now()]);

                $countSuccess++;
                $this->info("✓ Berhasil kirim ke {$customer->nama}.");
            } else {
                $countFailed++;
                $this->error("✗ Gagal kirim ke {$customer->nama}: " . ($result['message'] ?? 'Unknown error'));
            }
        }

        $this->info("\nSelesai! Terkirim: {$countSuccess} | Gagal: {$countFailed} | Dilewati: {$countSkipped}.");
        return 0;
    }
}
