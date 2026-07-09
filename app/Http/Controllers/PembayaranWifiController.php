<?php

namespace App\Http\Controllers;

use App\Models\CicilanPembayaran;
use App\Models\Pelanggan;
use App\Models\PembayaranWifi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PembayaranWifiController extends Controller
{
    public function index(Request $request): View
    {
        $query = CicilanPembayaran::with(['pembayaranWifi.pelanggan'])
            ->latest('tanggal_bayar');

        // Filter status tagihan
        if ($request->filled('status')) {
            $query->whereHas('pembayaranWifi', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        // Filter bulan
        if ($request->filled('bulan_tagihan')) {
            $query->whereHas('pembayaranWifi', function ($q) use ($request) {
                $q->where('bulan_tagihan', $request->bulan_tagihan);
            });
        }

        // Filter tahun
        if ($request->filled('tahun_tagihan')) {
            $query->whereHas('pembayaranWifi', function ($q) use ($request) {
                $q->where('tahun_tagihan', $request->tahun_tagihan);
            });
        }

        $riwayat = $query->paginate(15)->withQueryString();
        $bulanList = PembayaranWifi::$namaBulan;
        $tahunList = range(date('Y'), date('Y') - 3);

        return view('pembayaran-wifi.index', compact('riwayat', 'bulanList', 'tahunList'));
    }

    /**
     * Form proses pembayaran:
     * - Pilih pelanggan
     * - Tampil daftar tagihan belum lunas milik pelanggan (via AJAX)
     * - Input nominal bayar
     */
    public function create(): View
    {
        $pelanggan = Pelanggan::with('paketHarga')->orderBy('nama')->get();

        return view('pembayaran-wifi.create', compact('pelanggan'));
    }

    /**
     * API: Load tagihan belum lunas milik pelanggan tertentu.
     * Dipanggil oleh Alpine.js fetch saat pelanggan dipilih di form.
     */
    public function getTagihan(int $pelangganId): JsonResponse
    {
        $tagihan = PembayaranWifi::where('pelanggan_id', $pelangganId)
            ->belumLunas()
            ->orderBy('tahun_tagihan')
            ->orderBy('bulan_tagihan')
            ->get();

        $result = $tagihan->map(fn ($t) => [
            'id'              => $t->id,
            'label'           => $t->nama_bulan . ' ' . $t->tahun_tagihan,
            'total_tagihan'   => $t->total_tagihan,
            'nominal_dibayar' => $t->nominal_dibayar,
            'sisa_tagihan'    => $t->sisa_tagihan,
            'sisa_format'     => number_format($t->sisa_tagihan, 0, ',', '.'),
            'status'          => $t->status,
        ]);

        return response()->json($result);
    }

    /**
     * Proses simpan pembayaran.
     *
     * Logika:
     * - Ambil tagihan berdasarkan ID
     * - Pastikan belum lunas
     * - Hitung sisa setelah bayar
     * - Update status tagihan (Lunas / Cicilan)
     * - Simpan record cicilan baru
     * - Redirect ke cetak kuitansi
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pelanggan_id'    => 'required|exists:pelanggan,id',
            'tagihan_id'      => 'required|exists:pembayaran_wifi,id',
            'nominal_dibayar' => 'required|numeric|min:1',
            'tanggal_bayar'   => 'required|date',
        ]);

        $tagihan = PembayaranWifi::with('pelanggan')->findOrFail($validated['tagihan_id']);

        // Pastikan tagihan ini milik pelanggan yang dipilih
        if ($tagihan->pelanggan_id != $validated['pelanggan_id']) {
            return redirect()->route('pembayaran-wifi.create')
                ->with('error', 'Data tagihan tidak valid.')
                ->withInput();
        }

        // Pastikan belum lunas
        if ($tagihan->status === PembayaranWifi::STATUS_LUNAS) {
            return redirect()->route('pembayaran-wifi.create')
                ->with('error', "Tagihan {$tagihan->nama_bulan} {$tagihan->tahun_tagihan} sudah LUNAS.")
                ->withInput();
        }

        $nominalBayar = (float) $validated['nominal_dibayar'];

        // Nominal tidak boleh melebihi sisa tagihan
        if ($nominalBayar > $tagihan->sisa_tagihan) {
            return redirect()->route('pembayaran-wifi.create')
                ->with('error', 'Nominal pembayaran melebihi sisa tagihan (Rp ' . number_format($tagihan->sisa_tagihan, 0, ',', '.') . ').')
                ->withInput();
        }

        $nominalDibayarBaru = $tagihan->nominal_dibayar + $nominalBayar;
        $sisaBaru           = $tagihan->sisa_tagihan - $nominalBayar;
        $statusBaru         = ($sisaBaru <= 0)
            ? PembayaranWifi::STATUS_LUNAS
            : PembayaranWifi::STATUS_CICILAN;

        // Catat transaksi cicilan
        CicilanPembayaran::create([
            'pembayaran_wifi_id' => $tagihan->id,
            'tanggal_bayar'      => $validated['tanggal_bayar'],
            'nominal'            => $nominalBayar,
        ]);

        // Update tagihan utama
        $tagihan->update([
            'nominal_dibayar' => $nominalDibayarBaru,
            'sisa_tagihan'    => max(0, $sisaBaru),
            'status'          => $statusBaru,
        ]);

        return redirect()->route('tagihan-wifi.cetak', $tagihan->id)
            ->with('success', "Pembayaran berhasil. Status tagihan: {$statusBaru}.");
    }
}
