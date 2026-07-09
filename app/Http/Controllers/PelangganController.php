<?php

namespace App\Http\Controllers;

use App\Models\PaketHarga;
use App\Models\Pelanggan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PelangganController extends Controller
{
    public function index(): View
    {
        $pelanggan = Pelanggan::with('paketHarga')->latest()->paginate(15);

        return view('pelanggan.index', compact('pelanggan'));
    }

    public function create(): View
    {
        $paketHarga = PaketHarga::orderBy('nama_paket')->get();

        return view('pelanggan.form', [
            'pelanggan' => new Pelanggan(),
            'paketHarga' => $paketHarga,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama'               => 'required|string|max:150',
            'alamat'             => 'required|string',
            'no_hp'              => 'required|string|max:20',
            'tanggal_pembayaran' => 'required|integer|between:1,31',
            'paket_id'           => 'required|exists:paket_harga,id',
        ]);

        Pelanggan::create($validated);

        return redirect()->route('pelanggan.index')
            ->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function edit(Pelanggan $pelanggan): View
    {
        $paketHarga = PaketHarga::orderBy('nama_paket')->get();

        return view('pelanggan.form', compact('pelanggan', 'paketHarga'));
    }

    public function update(Request $request, Pelanggan $pelanggan): RedirectResponse
    {
        $validated = $request->validate([
            'nama'               => 'required|string|max:150',
            'alamat'             => 'required|string',
            'no_hp'              => 'required|string|max:20',
            'tanggal_pembayaran' => 'required|integer|between:1,31',
            'paket_id'           => 'required|exists:paket_harga,id',
        ]);

        $pelanggan->update($validated);

        return redirect()->route('pelanggan.index')
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Pelanggan $pelanggan): RedirectResponse
    {
        if ($pelanggan->pembayaranWifi()->exists()) {
            return redirect()->route('pelanggan.index')
                ->with('error', 'Pelanggan tidak dapat dihapus karena sudah memiliki riwayat pembayaran.');
        }

        $pelanggan->delete();

        return redirect()->route('pelanggan.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }
}
