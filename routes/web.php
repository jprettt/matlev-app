<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MatlevController;

// Halaman Login & Register
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Halaman Protected User (Wajib Login)
Route::middleware(['auth'])->group(function () {
    // Beranda / Landing Page dengan Hero Slider
    Route::get('/dashboard', [MatlevController::class, 'dashboard'])->name('user.dashboard');
    
    // Halaman Daftar Kriteria & Form Upload
    Route::get('/kriteria', [MatlevController::class, 'kriteria'])->name('user.kriteria');
    Route::get('/upload', [MatlevController::class, 'kriteria'])->name('user.upload'); // Alias backward compatibility

    // Halaman Dokumen Perlu Revisi
    Route::get('/revisi', [MatlevController::class, 'revisi'])->name('user.revisi');

    // Halaman Riwayat & Log Aktivitas
    Route::get('/riwayat', [MatlevController::class, 'riwayat'])->name('user.history');

    // Halaman Panduan Penggunaan
    Route::get('/panduan', [MatlevController::class, 'panduan'])->name('user.panduan');

    // Endpoint Upload Bukti Dokumen PDF (Fungsionalitas Tidak Diubah)
    Route::post('/upload/{maturityLevelId}', [MatlevController::class, 'upload'])->name('matlev.upload');
});