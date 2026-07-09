<?php

namespace App\Http\Controllers;

use App\Models\CicilanPembayaran;
use App\Models\DetailNotaCustom;
use App\Models\NotaCustom;
use App\Models\PaketHarga;
use App\Models\Pelanggan;
use App\Models\PembayaranWifi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    /**
     * GET /api/pelanggan
     * Get list of all pelanggan with their active packages.
     */
    public function getPelanggan(): JsonResponse
    {
        $pelanggan = Pelanggan::with('paketHarga')->orderBy('nama')->get();
        return response()->json([
            'success' => true,
            'data' => $pelanggan
        ]);
    }

    /**
     * GET /api/tagihan-wifi
     * Get all wifi bills (optional filters: pelanggan_id, status)
     */
    public function getTagihanWifi(Request $request): JsonResponse
    {
        $query = PembayaranWifi::with(['pelanggan.paketHarga', 'cicilanPembayaran'])->latest();

        if ($request->filled('pelanggan_id')) {
            $query->where('pelanggan_id', $request->pelanggan_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('bulan_tagihan')) {
            $query->where('bulan_tagihan', $request->bulan_tagihan);
        }

        if ($request->filled('tahun_tagihan')) {
            $query->where('tahun_tagihan', $request->tahun_tagihan);
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    /**
     * GET /api/tagihan-wifi/{id}
     * Get details of a single tagihan, including pelanggan details,
     * installment history, and previous unpaid bills (tunggakan).
     */
    public function getTagihanWifiDetail(int $id): JsonResponse
    {
        $tagihan = PembayaranWifi::with([
            'pelanggan.paketHarga',
            'cicilanPembayaran'
        ])->find($id);

        if (!$tagihan) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan tidak ditemukan'
            ], 404);
        }

        // Ambil tagihan belum lunas lainnya untuk pelanggan ini (tunggakan)
        $tunggakanLain = PembayaranWifi::where('pelanggan_id', $tagihan->pelanggan_id)
            ->where('id', '!=', $tagihan->id)
            ->belumLunas()
            ->orderBy('tahun_tagihan')
            ->orderBy('bulan_tagihan')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'tagihan' => $tagihan,
                'tunggakan_lain' => $tunggakanLain
            ]
        ]);
    }

    /**
     * GET /api/tagihan-wifi/cek-cicilan/{pelanggan_id}
     * Check previous unpaid bills/installments for warning banner.
     */
    public function cekCicilanLalu(int $pelangganId): JsonResponse
    {
        $tunggakan = PembayaranWifi::where('pelanggan_id', $pelangganId)
            ->belumLunas()
            ->orderBy('tahun_tagihan')
            ->orderBy('bulan_tagihan')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tunggakan
        ]);
    }

    /**
     * POST /api/tagihan-wifi
     * Create a new wifi bill (Status: Belum Dibayar)
     */
    public function storeTagihanWifi(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pelanggan_id'  => 'required|exists:pelanggan,id',
            'bulan_tagihan' => 'required|integer|between:1,12',
            'tahun_tagihan' => 'required|integer|min:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $pelanggan = Pelanggan::with('paketHarga')->findOrFail($request->pelanggan_id);
        $totalTagihan = $pelanggan->paketHarga->harga;

        // Cek duplicate bill
        $sudahAda = PembayaranWifi::where('pelanggan_id', $request->pelanggan_id)
            ->where('bulan_tagihan', $request->bulan_tagihan)
            ->where('tahun_tagihan', $request->tahun_tagihan)
            ->exists();

        if ($sudahAda) {
            $namaBulan = PembayaranWifi::$namaBulan[$request->bulan_tagihan];
            return response()->json([
                'success' => false,
                'message' => "Tagihan bulan {$namaBulan} {$request->tahun_tagihan} untuk pelanggan {$pelanggan->nama} sudah ada."
            ], 400);
        }

        $tagihan = PembayaranWifi::create([
            'pelanggan_id'    => $request->pelanggan_id,
            'bulan_tagihan'   => $request->bulan_tagihan,
            'tahun_tagihan'   => $request->tahun_tagihan,
            'total_tagihan'   => $totalTagihan,
            'nominal_dibayar' => 0,
            'sisa_tagihan'    => $totalTagihan,
            'status'          => PembayaranWifi::STATUS_BELUM_DIBAYAR,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tagihan berhasil dibuat',
            'data' => $tagihan
        ], 201);
    }

    /**
     * GET /api/pembayaran-wifi
     */
    public function getPembayaranWifi(Request $request): JsonResponse
    {
        $query = CicilanPembayaran::with(['pembayaranWifi.pelanggan'])
            ->latest('tanggal_bayar');

        if ($request->filled('status')) {
            $query->whereHas('pembayaranWifi', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        if ($request->filled('bulan_tagihan')) {
            $query->whereHas('pembayaranWifi', function ($q) use ($request) {
                $q->where('bulan_tagihan', $request->bulan_tagihan);
            });
        }

        if ($request->filled('tahun_tagihan')) {
            $query->whereHas('pembayaranWifi', function ($q) use ($request) {
                $q->where('tahun_tagihan', $request->tahun_tagihan);
            });
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    /**
     * POST /api/pembayaran-wifi
     * Store installment/payment for WiFi bill
     */
    public function storePembayaranWifi(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pelanggan_id'    => 'required|exists:pelanggan,id',
            'tagihan_id'      => 'required|exists:pembayaran_wifi,id',
            'nominal_dibayar' => 'required|numeric|min:1',
            'tanggal_bayar'   => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $tagihan = PembayaranWifi::find($request->tagihan_id);

        if ($tagihan->pelanggan_id != $request->pelanggan_id) {
            return response()->json([
                'success' => false,
                'message' => 'Data tagihan tidak cocok dengan pelanggan.'
            ], 400);
        }

        if ($tagihan->status === PembayaranWifi::STATUS_LUNAS) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan ini sudah lunas.'
            ], 400);
        }

        $nominalBayar = (float) $request->nominal_dibayar;

        if ($nominalBayar > $tagihan->sisa_tagihan) {
            return response()->json([
                'success' => false,
                'message' => 'Nominal pembayaran melebihi sisa tagihan (Maks: Rp ' . number_format($tagihan->sisa_tagihan, 0, ',', '.') . ').'
            ], 400);
        }

        $nominalDibayarBaru = $tagihan->nominal_dibayar + $nominalBayar;
        $sisaBaru           = $tagihan->sisa_tagihan - $nominalBayar;
        $statusBaru         = ($sisaBaru <= 0)
            ? PembayaranWifi::STATUS_LUNAS
            : PembayaranWifi::STATUS_CICILAN;

        DB::transaction(function () use ($tagihan, $nominalBayar, $nominalDibayarBaru, $sisaBaru, $statusBaru, $request) {
            // Catat history cicilan
            CicilanPembayaran::create([
                'pembayaran_wifi_id' => $tagihan->id,
                'tanggal_bayar'      => $request->tanggal_bayar,
                'nominal'            => $nominalBayar,
            ]);

            // Update status tagihan utama
            $tagihan->update([
                'nominal_dibayar' => $nominalDibayarBaru,
                'sisa_tagihan'    => max(0, $sisaBaru),
                'status'          => $statusBaru,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil disimpan',
            'data' => $tagihan->fresh(['cicilanPembayaran'])
        ]);
    }

    /**
     * GET /api/nota-custom
     * Get list of custom bills.
     */
    public function getNotaCustom(): JsonResponse
    {
        $nota = NotaCustom::withCount('detailNota')->latest()->get();
        return response()->json([
            'success' => true,
            'data' => $nota
        ]);
    }

    /**
     * GET /api/nota-custom/{id}
     * Get detail of a custom bill including items.
     */
    public function getNotaCustomDetail(int $id): JsonResponse
    {
        $nota = NotaCustom::with('detailNota')->find($id);

        if (!$nota) {
            return response()->json([
                'success' => false,
                'message' => 'Nota tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $nota
        ]);
    }

    /**
     * POST /api/nota-custom
     * Create custom bill (master-detail transaction)
     */
    public function storeNotaCustom(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_pembeli'          => 'required|string|max:255',
            'tanggal'               => 'required|date',
            'items'                 => 'required|array|min:1',
            'items.*.nama_item'     => 'required|string|max:255',
            'items.*.kuantitas'     => 'required|integer|min:1',
            'items.*.harga_satuan'  => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $tanggal = \Carbon\Carbon::parse($request->tanggal);
        $prefix = 'NC-' . $tanggal->format('Ym') . '-';

        try {
            $nota = DB::transaction(function () use ($request, $prefix, $tanggal) {
                // Generate sequential receipt ID
                $lastNota = NotaCustom::where('nomor_nota', 'like', $prefix . '%')
                    ->orderBy('nomor_nota', 'desc')
                    ->lockForUpdate()
                    ->first();

                $nextNumber = 1;
                if ($lastNota) {
                    $parts = explode('-', $lastNota->nomor_nota);
                    $lastSeq = (int) end($parts);
                    $nextNumber = $lastSeq + 1;
                }

                $nomorNota = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

                // Insert Master Nota Custom
                $notaCustom = NotaCustom::create([
                    'nomor_nota'   => $nomorNota,
                    'nama_pembeli' => $request->nama_pembeli,
                    'tanggal'      => $tanggal,
                    'total_harga'  => 0, // diupdate setelah hitung detail
                ]);

                $totalHarga = 0;

                // Insert Detail Items
                foreach ($request->items as $item) {
                    $qty   = (int) $item['kuantitas'];
                    $price = (float) $item['harga_satuan'];
                    $subtotal = $qty * $price;
                    $totalHarga += $subtotal;

                    DetailNotaCustom::create([
                        'nota_custom_id' => $notaCustom->id,
                        'nama_item'      => $item['nama_item'],
                        'kuantitas'      => $qty,
                        'harga_satuan'   => $price,
                        'subtotal'       => $subtotal,
                    ]);
                }

                // Update Master Total Harga
                $notaCustom->update(['total_harga' => $totalHarga]);

                return $notaCustom;
            });

            return response()->json([
                'success' => true,
                'message' => 'Nota Custom berhasil dibuat',
                'data' => $nota->load('detailNota')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/wa-status
     */
    public function getWaStatus(): JsonResponse
    {
        $fonnte = new \App\Services\FonnteService();
        $status = $fonnte->getDeviceStatus();

        return response()->json([
            'success' => true,
            'data' => $status
        ]);
    }

    /**
     * GET /api/wa-history
     */
    public function getWaHistory(Request $request): JsonResponse
    {
        $query = \App\Models\WaChatHistory::with('pelanggan')->latest();

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    /**
     * GET /api/send-billing-reminders
     */
    public function triggerBillingReminders(): JsonResponse
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('app:send-billing-reminders');
            $output = \Illuminate\Support\Facades\Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Billing reminders triggered successfully.',
                'output' => trim($output)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pengingat tagihan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/pelanggan
     */
    public function storePelanggan(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama'               => 'required|string|max:150',
            'alamat'             => 'required|string',
            'no_hp'              => 'required|string|max:20',
            'tanggal_pembayaran' => 'required|integer|between:1,31',
            'paket_id'           => 'required|exists:paket_harga,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $pelanggan = Pelanggan::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil ditambahkan.',
            'data' => $pelanggan->load('paketHarga')
        ], 201);
    }

    /**
     * PUT /api/pelanggan/{id}
     */
    public function updatePelanggan(Request $request, $id): JsonResponse
    {
        $pelanggan = Pelanggan::find($id);
        if (!$pelanggan) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan tidak ditemukan.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama'               => 'required|string|max:150',
            'alamat'             => 'required|string',
            'no_hp'              => 'required|string|max:20',
            'tanggal_pembayaran' => 'required|integer|between:1,31',
            'paket_id'           => 'required|exists:paket_harga,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $pelanggan->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil diperbarui.',
            'data' => $pelanggan->load('paketHarga')
        ]);
    }

    /**
     * DELETE /api/pelanggan/{id}
     */
    public function deletePelanggan($id): JsonResponse
    {
        $pelanggan = Pelanggan::find($id);
        if (!$pelanggan) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan tidak ditemukan.'
            ], 404);
        }

        if ($pelanggan->pembayaranWifi()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan tidak dapat dihapus karena sudah memiliki riwayat pembayaran.'
            ], 422);
        }

        $pelanggan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil dihapus.'
        ]);
    }

    /**
     * GET /api/paket-harga
     */
    public function getPaketHarga(): JsonResponse
    {
        $paketHarga = PaketHarga::orderBy('nama_paket')->get();
        return response()->json([
            'success' => true,
            'data' => $paketHarga
        ]);
    }

    /**
     * POST /api/paket-harga
     */
    public function storePaketHarga(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_paket' => 'required|string|max:100',
            'harga'      => 'required|numeric|min:0',
            'deskripsi'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $paketHarga = PaketHarga::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Paket harga berhasil ditambahkan.',
            'data' => $paketHarga
        ], 201);
    }

    /**
     * PUT /api/paket-harga/{id}
     */
    public function updatePaketHarga(Request $request, $id): JsonResponse
    {
        $paketHarga = PaketHarga::find($id);
        if (!$paketHarga) {
            return response()->json([
                'success' => false,
                'message' => 'Paket harga tidak ditemukan.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_paket' => 'required|string|max:100',
            'harga'      => 'required|numeric|min:0',
            'deskripsi'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $paketHarga->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Paket harga berhasil diperbarui.',
            'data' => $paketHarga
        ]);
    }

    /**
     * DELETE /api/paket-harga/{id}
     */
    public function deletePaketHarga($id): JsonResponse
    {
        $paketHarga = PaketHarga::find($id);
        if (!$paketHarga) {
            return response()->json([
                'success' => false,
                'message' => 'Paket harga tidak ditemukan.'
            ], 404);
        }

        if ($paketHarga->pelanggan()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Paket harga tidak dapat dihapus karena masih digunakan oleh pelanggan.'
            ], 422);
        }

        $paketHarga->delete();

        return response()->json([
            'success' => true,
            'message' => 'Paket harga berhasil dihapus.'
        ]);
    }

    /**
     * POST /api/wa-test-send
     */
    public function postWaTestSend(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'target'  => 'required|string',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $target = $request->target;
        if (str_starts_with($target, '0')) {
            $target = '62' . substr($target, 1);
        }

        $fonnte = new \App\Services\FonnteService();
        $result = $fonnte->sendMessage($target, $request->message);

        if ($result['success']) {
            \App\Models\WaChatHistory::create([
                'target' => $request->target,
                'message' => $request->message,
                'status' => 'sent',
                'response' => json_encode($result['raw'] ?? []),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pesan uji coba berhasil terkirim ke WhatsApp.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengirim pesan: ' . ($result['message'] ?? 'Unknown error')
        ], 500);
    }
}
