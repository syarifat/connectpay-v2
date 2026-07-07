<?php

namespace App\Http\Controllers;

use App\Models\PaketHarga;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaketHargaController extends Controller
{
    public function index(): View
    {
        $paket = PaketHarga::latest()->paginate(10);

        return view('paket-harga.index', compact('paket'));
    }

    public function create(): View
    {
        return view('paket-harga.form', ['paket' => new PaketHarga()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_paket' => 'required|string|max:100',
            'harga'      => 'required|numeric|min:0',
            'deskripsi'  => 'nullable|string',
        ]);

        PaketHarga::create($validated);

        return redirect()->route('paket-harga.index')
            ->with('success', 'Paket harga berhasil ditambahkan.');
    }

    public function edit(PaketHarga $paketHarga): View
    {
        return view('paket-harga.form', ['paket' => $paketHarga]);
    }

    public function update(Request $request, PaketHarga $paketHarga): RedirectResponse
    {
        $validated = $request->validate([
            'nama_paket' => 'required|string|max:100',
            'harga'      => 'required|numeric|min:0',
            'deskripsi'  => 'nullable|string',
        ]);

        $paketHarga->update($validated);

        return redirect()->route('paket-harga.index')
            ->with('success', 'Paket harga berhasil diperbarui.');
    }

    public function destroy(PaketHarga $paketHarga): RedirectResponse
    {
        // Cek apakah paket masih digunakan oleh pelanggan
        if ($paketHarga->pelanggan()->exists()) {
            return redirect()->route('paket-harga.index')
                ->with('error', 'Paket tidak dapat dihapus karena masih digunakan oleh pelanggan.');
        }

        $paketHarga->delete();

        return redirect()->route('paket-harga.index')
            ->with('success', 'Paket harga berhasil dihapus.');
    }
}
