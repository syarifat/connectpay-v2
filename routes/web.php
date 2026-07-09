<?php

use App\Http\Controllers\FonnteController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\NotaCustomController;
use App\Http\Controllers\PaketHargaController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PembayaranWifiController;
use App\Http\Controllers\TagihanWifiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - ConnectPay
|--------------------------------------------------------------------------
*/

// ─── Authentication Routes (Public) ─────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::any('/logout', [LoginController::class, 'logout'])->name('logout');

// ─── Customer Public Links (No Auth Required) ────────────────────────────────
// Cetak kuitansi tagihan
Route::get('tagihan-wifi/{id}/cetak', [TagihanWifiController::class, 'cetak'])
    ->name('tagihan-wifi.cetak');

// Render image tagihan
Route::get('tagihan-wifi/{id}/image', [TagihanWifiController::class, 'generateImage'])
    ->name('tagihan-wifi.image');

// Cetak kuitansi nota custom
Route::get('nota-custom/{id}/cetak', [NotaCustomController::class, 'cetak'])
    ->name('nota-custom.cetak');


// ─── Admin Protected Panel (Auth Required) ───────────────────────────────────
Route::middleware('admin.auth')->group(function () {
    
    Route::get('/', function () {
        return redirect()->route('tagihan-wifi.index');
    });

    // ─── Master Data ─────────────────────────────────────────────────────────
    // Master: Paket Harga
    Route::resource('paket-harga', PaketHargaController::class)->except(['show']);

    // Master: Pelanggan
    Route::resource('pelanggan', PelangganController::class)->except(['show']);

    // ─── Tagihan WiFi (Buat & Kelola Tagihan) ────────────────────────────────
    Route::resource('tagihan-wifi', TagihanWifiController::class)
        ->except(['show', 'edit', 'update', 'destroy']);

    // API: cek cicilan belum lunas milik pelanggan (untuk warning di form buat tagihan)
    Route::get('tagihan-wifi/api/cek-cicilan/{pelanggan_id}', [TagihanWifiController::class, 'cekCicilanLalu'])
        ->name('tagihan-wifi.cek-cicilan');

    // ─── Pembayaran WiFi (Proses Bayar) ──────────────────────────────────────
    Route::resource('pembayaran-wifi', PembayaranWifiController::class)
        ->except(['show', 'edit', 'update', 'destroy']);

    // API: load tagihan belum lunas milik pelanggan (untuk dropdown di form bayar)
    Route::get('pembayaran-wifi/api/tagihan/{pelanggan_id}', [PembayaranWifiController::class, 'getTagihan'])
        ->name('pembayaran-wifi.get-tagihan');

    // ─── Nota Custom (Non-WiFi) ──────────────────────────────────────────────
    Route::resource('nota-custom', NotaCustomController::class)
        ->except(['show', 'edit', 'update', 'destroy']);

    // ─── Fonnte & WA Logs ────────────────────────────────────────────────────
    Route::get('wa-status', [FonnteController::class, 'status'])
        ->name('wa-status.index');
    Route::get('wa-history', [FonnteController::class, 'history'])
        ->name('wa-history.index');
    Route::post('wa-test-send', [FonnteController::class, 'testSend'])
        ->name('wa-status.test-send');
});
