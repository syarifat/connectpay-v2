<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/pelanggan', [ApiController::class, 'getPelanggan']);

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
Route::get('/send-billing-reminders', [ApiController::class, 'triggerBillingReminders']);
