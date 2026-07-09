<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/pelanggan', [ApiController::class, 'getPelanggan']);
Route::post('/pelanggan', [ApiController::class, 'storePelanggan']);
Route::put('/pelanggan/{id}', [ApiController::class, 'updatePelanggan']);
Route::delete('/pelanggan/{id}', [ApiController::class, 'deletePelanggan']);

Route::get('/paket-harga', [ApiController::class, 'getPaketHarga']);
Route::post('/paket-harga', [ApiController::class, 'storePaketHarga']);
Route::put('/paket-harga/{id}', [ApiController::class, 'updatePaketHarga']);
Route::delete('/paket-harga/{id}', [ApiController::class, 'deletePaketHarga']);

Route::get('/tagihan-wifi', [ApiController::class, 'getTagihanWifi']);
Route::get('/tagihan-wifi/cek-cicilan/{pelanggan_id}', [ApiController::class, 'cekCicilanLalu']);
Route::get('/tagihan-wifi/{id}', [ApiController::class, 'getTagihanWifiDetail']);
Route::post('/tagihan-wifi', [ApiController::class, 'storeTagihanWifi']);

Route::get('/pembayaran-wifi', [ApiController::class, 'getPembayaranWifi']);
Route::post('/pembayaran-wifi', [ApiController::class, 'storePembayaranWifi']);

Route::get('/nota-custom', [ApiController::class, 'getNotaCustom']);
Route::get('/nota-custom/{id}', [ApiController::class, 'getNotaCustomDetail']);
Route::post('/nota-custom', [ApiController::class, 'storeNotaCustom']);

Route::get('/wa-status', [ApiController::class, 'getWaStatus']);
Route::get('/wa-history', [ApiController::class, 'getWaHistory']);
Route::post('/wa-test-send', [ApiController::class, 'postWaTestSend']);
Route::get('/send-billing-reminders', [ApiController::class, 'triggerBillingReminders']);
