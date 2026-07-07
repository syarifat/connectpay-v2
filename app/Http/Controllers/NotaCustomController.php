<?php

namespace App\Http\Controllers;

use App\Models\DetailNotaCustom;
use App\Models\NotaCustom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NotaCustomController extends Controller
{
    public function index(): View
    {
        $nota = NotaCustom::latest()->paginate(15);

        return view('nota-custom.index', compact('nota'));
    }

    public function create(): View
    {
        return view('nota-custom.create');
    }

    /**
     * Simpan nota custom beserta detail item menggunakan DB Transaction.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_pembeli'        => 'required|string|max:150',
            'tanggal'             => 'required|date',
            'items'               => 'required|array|min:1',
            'items.*.nama_item'   => 'required|string|max:200',
            'items.*.kuantitas'   => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        // Generate nomor nota otomatis: NC-YYYYMM-XXXX
        $prefix = 'NC-' . date('Ym') . '-';
        $lastNota = NotaCustom::where('nomor_nota', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $urutan = $lastNota
            ? (int) substr($lastNota->nomor_nota, -4) + 1
            : 1;

        $nomorNota = $prefix . str_pad($urutan, 4, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($request, $nomorNota) {
            // Hitung total harga dari semua item
            $totalHarga = collect($request->items)->sum(function ($item) {
                return (float) $item['harga_satuan'] * (int) $item['kuantitas'];
            });

            // Simpan header nota
            $nota = NotaCustom::create([
                'nomor_nota'   => $nomorNota,
                'tanggal'      => $request->tanggal,
                'nama_pembeli' => $request->nama_pembeli,
                'total_harga'  => $totalHarga,
            ]);

            // Simpan detail item
            $detailItems = collect($request->items)->map(function ($item) use ($nota) {
                $subtotal = (float) $item['harga_satuan'] * (int) $item['kuantitas'];

                return [
                    'nota_custom_id' => $nota->id,
                    'nama_item'      => $item['nama_item'],
                    'kuantitas'      => (int) $item['kuantitas'],
                    'harga_satuan'   => (float) $item['harga_satuan'],
                    'subtotal'       => $subtotal,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            })->toArray();

            DetailNotaCustom::insert($detailItems);

            // Simpan nomor nota di session untuk redirect ke cetak
            session(['last_nota_id' => $nota->id]);
        });

        return redirect()->route('nota-custom.cetak', session('last_nota_id'))
            ->with('success', "Nota {$nomorNota} berhasil disimpan.");
    }

    /**
     * Halaman cetak nota custom.
     */
    public function cetak(int $id): View
    {
        $nota = NotaCustom::with('detailNota')->findOrFail($id);

        return view('nota-custom.cetak', compact('nota'));
    }
}
