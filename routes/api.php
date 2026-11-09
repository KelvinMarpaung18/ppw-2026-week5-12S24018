<?php

use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\BukuApiController;
use App\Http\Controllers\Api\V1\PeminjamanApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // =========================================================================
    // 1. ENDPOINT PUBLIK (Dapat diakses tanpa token: Login & Katalog Baca)
    // =========================================================================
    Route::post('/auth/login', [AuthTokenController::class, 'login']);

    // Katalog Buku & Pengayaan Referensi Eksternal (Read-Only)
    Route::get('/bukus', [BukuApiController::class, 'index']);
    Route::get('/bukus/{identifier}', [BukuApiController::class, 'show']);
    Route::get('/bukus/{id}/referensi-eksternal', [BukuApiController::class, 'referensiEksternal']);

    // =========================================================================
    // 2. ENDPOINT TERPROTEKSI STATELESS BEARER TOKEN (Wajib auth:sanctum)
    // =========================================================================
    Route::middleware('auth:sanctum')->group(function () {
        // Profil & Pencabutan Token Pustakawan
        Route::get('/auth/me', [AuthTokenController::class, 'me']);
        Route::post('/auth/logout', [AuthTokenController::class, 'logout']);

        // Mutasi Katalog Buku (Store, Put/Full, Patch/Partial, Delete)
        Route::post('/bukus', [BukuApiController::class, 'store']);
        Route::put('/bukus/{id}', [BukuApiController::class, 'update']);
        Route::patch('/bukus/{id}', [BukuApiController::class, 'update']);
        Route::delete('/bukus/{id}', [BukuApiController::class, 'destroy']);

        // Transaksi Sirkulasi Peminjaman & Pengembalian Buku (ACID + Lock)
        Route::get('/peminjamans', [PeminjamanApiController::class, 'index']);
        Route::get('/peminjamans/{id}', [PeminjamanApiController::class, 'show']);
        Route::post('/peminjamans', [PeminjamanApiController::class, 'store']);
        Route::patch('/peminjamans/{id}/kembalikan', [PeminjamanApiController::class, 'kembalikan']);
        Route::delete('/peminjamans/{id}', [PeminjamanApiController::class, 'destroy']);
    });
});
