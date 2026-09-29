<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PresensiSholatController;
use App\Http\Controllers\SantriController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\IzinRequestController;
use App\Http\Controllers\InputCepatController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KamarController;
use App\Http\Controllers\PanelSayaController;
use App\Http\Controllers\KritikSaranController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\PesanController;

// Auth Routes (guest only) - Registrasi publik, akun dibuat sebagai Pengasuh
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
Route::post('/daftar', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');

// Bantuan login: cari akun berdasarkan nama (publik)
Route::get('/akun/cari', [AkunController::class, 'cari'])->name('akun.cari');

// PWA: manifest & service worker disajikan via route agar MIME konsisten
Route::get('/manifest.json', function () {
    $f = public_path('manifest.json');
    return response(file_exists($f) ? file_get_contents($f) : '{}', 200)
        ->header('Content-Type', 'application/manifest+json')
        ->header('Cache-Control', 'public, max-age=86400');
})->name('pwa.manifest');

Route::get('/sw.js', function () {
    $f = public_path('sw.js');
    return response(file_exists($f) ? file_get_contents($f) : '', 200)
        ->header('Content-Type', 'text/javascript')
        ->header('Cache-Control', 'no-cache')
        ->header('Service-Worker-Allowed', '/');
})->name('pwa.sw');

// Protected Routes
Route::middleware('auth')->group(function () {

    // Presensi Routes (baca: semua role)
    Route::get('/', [PresensiSholatController::class, 'index'])->name('presensi.index');

    // Lazy-load daftar nama untuk modal detail (semua role; scoping nanti di controller)
    Route::get('/presensi/detail', [PresensiSholatController::class, 'detail'])->name('presensi.detail');

    // Riwayat perubahan presensi
    Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat.index');

    // Panel Saya (pengasuh/personal): kalender, statistik, riwayat pribadi
    Route::get('/panel-saya', [PanelSayaController::class, 'index'])->name('panel-saya.index');

    // Notifikasi personal (semua role): badge & dropdown di header
    Route::get('/notifikasi-user/jumlah', [NotifikasiController::class, 'userUnreadCount'])->name('user-notif.unread');
    Route::get('/notifikasi-user/daftar', [NotifikasiController::class, 'userList'])->name('user-notif.list');

    // Pesan admin (pengasuh/personal): badge, list, dan tandai dibaca
    Route::get('/pesan-user/jumlah', [PesanController::class, 'userJumlah'])->name('pesan-user.jumlah');
    Route::get('/pesan-user/daftar', [PesanController::class, 'userDaftar'])->name('pesan-user.daftar');
    Route::post('/pesan-user/baca-semua', [PesanController::class, 'userMarkAllRead'])->name('pesan-user.baca-semua');
    Route::post('/pesan-user/{id}/baca', [PesanController::class, 'userMarkRead'])->name('pesan-user.baca');
    Route::delete('/pesan-user/{id}', [PesanController::class, 'userDestroy'])->name('pesan-user.hapus');

    // Pengajuan izin
    Route::get('/izin', [IzinRequestController::class, 'index'])->name('izin.index');
    Route::get('/izin/buat', [IzinRequestController::class, 'create'])->name('izin.create');

    // Kritik & Saran (pengasuh mengirim; halaman khusus pengasuh)
    Route::get('/kritik-saran', [KritikSaranController::class, 'create'])->name('kritik-saran.create');
    Route::post('/kritik-saran', [KritikSaranController::class, 'store'])->name('kritik-saran.store');

    // Daftar santri (baca: semua role)
    Route::get('/santri', [SantriController::class, 'index'])->name('santri.index');

    // Pengajuan izin: semua role bisa kirim
    Route::post('/izin', [IzinRequestController::class, 'store'])->name('izin.store');
    Route::get('/izin/{izin}/edit', [IzinRequestController::class, 'edit'])->name('izin.edit');
    Route::put('/izin/{izin}', [IzinRequestController::class, 'update'])->name('izin.update');

    // Aksi tulis: hanya admin & musyrif
    Route::middleware('role:admin,musyrif')->group(function () {
        // View Rekap & Ranking (menampilkan semua santri): hanya admin & musyrif
        Route::get('/rekap/cetak', [PresensiSholatController::class, 'cetakRekap'])->name('presensi.rekap.cetak');
        Route::get('/ranking-alfa', [PresensiSholatController::class, 'rankingAlfa'])->name('presensi.rankingAlfa');
        Route::get('/ranking-berjamaah', [PresensiSholatController::class, 'rankingBerjamaah'])->name('presensi.rankingBerjamaah');
        Route::get('/rekap-berjamaah', [PresensiSholatController::class, 'rekapBerjamaah'])->name('presensi.rekapBerjamaah');

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

        // Izin: approval saja (admin & musyrif)
        Route::post('/izin/{izin}/setujui', [IzinRequestController::class, 'setujui'])->name('izin.setujui');
        Route::post('/izin/{izin}/tolak', [IzinRequestController::class, 'tolak'])->name('izin.tolak');

        // Kamar Management
        Route::get('/kamar', [KamarController::class, 'index'])->name('kamar.index');
        Route::post('/kamar', [KamarController::class, 'store'])->name('kamar.store');
        Route::put('/kamar/{kamar}', [KamarController::class, 'update'])->name('kamar.update');
        Route::delete('/kamar/{kamar}', [KamarController::class, 'destroy'])->name('kamar.destroy');

        // Pesan admin ke santri
        Route::get('/pesan', [PesanController::class, 'index'])->name('pesan.index');
        Route::post('/pesan', [PesanController::class, 'store'])->name('pesan.store');
        Route::delete('/pesan/{batchId}', [PesanController::class, 'destroy'])->name('pesan.destroy');
    });

    // Hanya admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/user', [UserController::class, 'index'])->name('user.index');
        Route::post('/user', [UserController::class, 'store'])->name('user.store');
        Route::delete('/user/{user}', [UserController::class, 'destroy'])->name('user.destroy');
        Route::post('/user/{user}/reset-password', [UserController::class, 'resetPassword'])->name('user.resetPassword');
        Route::put('/user/{user}/role', [UserController::class, 'updateRole'])->name('user.updateRole');

        // Kelola Kritik & Saran (khusus admin)
        Route::get('/kritik-saran/kelola', [KritikSaranController::class, 'index'])->name('kritik-saran.index');
        Route::post('/kritik-saran/{kritik}/tandai', [KritikSaranController::class, 'markRead'])->name('kritik-saran.markRead');
        Route::delete('/kritik-saran/{kritik}', [KritikSaranController::class, 'destroy'])->name('kritik-saran.destroy');

        Route::get('/notifikasi/jumlah', [NotifikasiController::class, 'unreadCount'])->name('admin-notif.unread');
        Route::get('/notifikasi/daftar', [NotifikasiController::class, 'list'])->name('admin-notif.list');
        Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'markAllRead'])->name('admin-notif.markAllRead');
        Route::post('/notifikasi/{id}/baca', [NotifikasiController::class, 'markRead'])->name('admin-notif.markRead');
    });

    // Change Password
    Route::get('/ganti-password', [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('/ganti-password', [AuthController::class, 'updatePassword'])->name('password.update');

    // Profil
    Route::get('/profil', [AuthController::class, 'showProfile'])->name('profile.show');
    Route::put('/profil', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profil/font-size', [AuthController::class, 'updateFontSize'])->name('profile.font-size');
});
