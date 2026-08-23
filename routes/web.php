<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PresensiSholatController;
use App\Http\Controllers\SantriController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\IzinRequestController;
use App\Http\Controllers\InputCepatController;

// Auth Routes (guest only)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
Route::post('/daftar', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');

// Protected Routes
Route::middleware('auth')->group(function () {

    // Presensi Routes (baca: semua role)
    Route::get('/', [PresensiSholatController::class, 'index'])->name('presensi.index');
    Route::get('/rekap', [PresensiSholatController::class, 'rekap'])->name('presensi.rekap');
    Route::get('/rekap/cetak', [PresensiSholatController::class, 'cetakRekap'])->name('presensi.rekap.cetak');
    Route::get('/ranking-alfa', [PresensiSholatController::class, 'rankingAlfa'])->name('presensi.rankingAlfa');
    Route::get('/ranking-berjamaah', [PresensiSholatController::class, 'rankingBerjamaah'])->name('presensi.rankingBerjamaah');
    Route::get('/rekap-berjamaah', [PresensiSholatController::class, 'rekapBerjamaah'])->name('presensi.rekapBerjamaah');

    // Riwayat perubahan presensi
    Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat.index');

    // Pengajuan izin
    Route::get('/izin', [IzinRequestController::class, 'index'])->name('izin.index');
    Route::get('/izin/buat', [IzinRequestController::class, 'create'])->name('izin.create');

    // Daftar santri (baca: semua role)
    Route::get('/santri', [SantriController::class, 'index'])->name('santri.index');

    // Aksi tulis: hanya admin & musyrif
    Route::middleware('role:admin,musyrif')->group(function () {
        // Input cepat (ramah HP) — halaman input presensi
        Route::get('/input-cepat', [InputCepatController::class, 'index'])->name('input-cepat.index');
        Route::post('/presensi/simpan', [PresensiSholatController::class, 'store'])->name('presensi.store');
        Route::post('/presensi/quick-status', [PresensiSholatController::class, 'quickStatus'])->name('presensi.quickStatus');
        Route::post('/presensi/hapus', [PresensiSholatController::class, 'hapusPresensi'])->name('presensi.hapus');

        Route::get('/rekap-berjamaah/export', [PresensiSholatController::class, 'exportRekapBerjamaah'])->name('presensi.rekapBerjamaah.export');

        // Santri Management Routes
        Route::resource('santri', SantriController::class)->except(['index', 'show']);
        Route::post('/santri/{santri}/toggle-status', [SantriController::class, 'toggleStatus'])->name('santri.toggleStatus');
        Route::get('/santri-import', [SantriController::class, 'importForm'])->name('santri.import.form');
        Route::post('/santri-import', [SantriController::class, 'importStore'])->name('santri.import.store');

        // Izin: buat pengajuan & approval
        Route::post('/izin', [IzinRequestController::class, 'store'])->name('izin.store');
        Route::post('/izin/{izin}/setujui', [IzinRequestController::class, 'setujui'])->name('izin.setujui');
        Route::post('/izin/{izin}/tolak', [IzinRequestController::class, 'tolak'])->name('izin.tolak');
    });

    // Change Password
    Route::get('/ganti-password', [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('/ganti-password', [AuthController::class, 'updatePassword'])->name('password.update');
});
