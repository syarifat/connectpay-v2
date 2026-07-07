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
}
