<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\TrackingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Semua route di sini otomatis mendapat middleware 'api' yang:
| - Selalu return JSON response (termasuk error)
| - Rate limited (60 req/menit)
| - Tidak membutuhkan CSRF
|
| Untuk mengakses dari browser, gunakan Accept: application/json header.
| CORS sudah dikonfigurasi untuk akses cross-origin.
|
*/

Route::get('/track/{trackingCode}', [TrackingController::class, 'show'])->name('api.track');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{service}', [ServiceController::class, 'show']);
});
