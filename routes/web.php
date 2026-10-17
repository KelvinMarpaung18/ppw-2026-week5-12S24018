<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamanController;
use Illuminate\Support\Facades\Route;

// Rute Publik (Dapat Diakses Tamu & Mahasiswa)
Route::get('/', function () {
    return redirect()->route('buku.index');
});

Route::get('/buku', [BukuController::class, 'index'])->name('buku.index');

// Rute Autentikasi Tamu (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Rute Terproteksi Khusus Pustakawan (Auth Middleware)
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Mutasi Data Buku (Create, Store, Edit, Update, Destroy)
    Route::get('/buku/create', [BukuController::class, 'create'])->name('buku.create');
    Route::post('/buku', [BukuController::class, 'store'])->name('buku.store');
    Route::get('/buku/{buku}/edit', [BukuController::class, 'edit'])->name('buku.edit');
    Route::put('/buku/{buku}', [BukuController::class, 'update'])->name('buku.update');
    Route::delete('/buku/{buku}', [BukuController::class, 'destroy'])->name('buku.destroy');

    // Manajemen Kategori Buku (Resource CRUD)
    Route::resource('kategori', KategoriController::class);

    // Sirkulasi Transaksi Peminjaman & Pengembalian Buku Berbasis ACID
    Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
    Route::get('/peminjaman/create', [PeminjamanController::class, 'create'])->name('peminjaman.create');
    Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
    Route::patch('/peminjaman/{peminjaman}/kembalikan', [PeminjamanController::class, 'kembalikan'])->name('peminjaman.kembalikan');
});

// Detail Buku Publik (Diletakkan setelah /buku/create agar tidak bentrok)
Route::get('/buku/{buku}', [BukuController::class, 'show'])->name('buku.show');

