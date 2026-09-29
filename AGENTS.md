# SIJAMAAH — Agent Working Notes

Proyek: Aplikasi presensi sholat berjamaah pondok pesantren (Laravel 10 + Tailwind + Blade).

## Environment
- Lokal: `C:\laragon\www\sijamaah-app` (Laragon, Windows, PowerShell 5.1)
- Server: InfinityFree (`http://ftpupload.net`), path `htdocs/`, login FTP tersimpan di riwayat sesi opencode (bukan di file ini — jangan commit kredensial).
- Build CSS aktif: `public\build\assets\app-fNfoOVRA.css` (jangan rebuild sembarangan).

## Alur Deploy ke Server
1. Compile blade via `_compile_test.php` sementara, lalu hapus.
2. Upload file: `curl.exe -sS --ftp-create-dirs -T <file_lokal> "ftp://ftpupload.net/htdocs/<path>" --user <user:pass>`.
3. Upload `clear-views.php` ke `htdocs/`.
4. Jalankan `python check_server.py` — melewati anti-bot slowAES lalu memicu `clear-views.php` (self-delete) untuk membersihkan compiled blade di server (storage/framework/views).
5. Verify: download ulang & cek hash IDENTICAL dengan lokal.

## Struktur Halaman Penting
- `resources/views/partials/app-header.blade.php` — header semua halaman; berisi drawer sidebar + overlay (STYLE DITANGANI INLINE `<style>` di file ini).
- `resources/views/presensi/index.blade.php` — dashboard admin; berisi diagram per orang (stacked bar) dengan filter.
- `resources/views/input-cepat/index.blade.php` — punya drawer/kop CSS sendiri yang harus disesuaikan bila drawer global diubah.
- `app/Http/Controllers/PresensiSholatController.php` — `buildPerOrangData()` menghasilkan data diagram per orang; sorting sudah dikondisikan berdasar filter keterangan (Alfa→urut alfa, Izin→urut izin, dst; 'all'→persentase+hadir; mode personal→urut nama).

## Desain Header & Drawer (Kesepakatan Final dengan User)
- **Header 1** (logo pondok): ikut scroll/hilang.
- **Header 2** (kartu admin): diam menempel di atas. BUKAN `position:sticky` (gagal di mobile) — pakai JS `stickHeader2()`: set `position:fixed; top:0; zIndex:999` via `requestAnimationFrame`/scroll listener (passive), ID elemen = `header2`.
- **Drawer sidebar** (`#navPanel`): muncul dari KIRI seperti tirai → animasi `left: -300px → 0`, `transition: left 0.4s cubic-bezier(0.4,0,0.2,1)`, `z-index: 2147483647`, mulai TEPAT di BAWAH Header 2.
- **Overlay** (`#navOverlay`): `z-index: 998` (DI BAWAH Header 2 z-999 agar header tetap terlihat), `background: rgba(0,0,0,0.2)`, `backdrop-filter: blur(2px)` (jangan pekat).
- **Scroll lock**: `body.nav-open { position:fixed; left:0; right:0 }` + `body.style.top = '-' + scrollY`, restore via `window.scrollTo(0, scrollY)`.

## Bug yang Sudah Pernah Terjadi & Solusinya
- `backdrop-filter` pada Header 2 membuat `position:fixed` anak ter-clip → `<aside id="navPanel">` HARUS di luar elemen header 2.
- CSS `!important` mengalahkan `element.style.top` inline → pakai `setProperty('top', v, 'important')`.
- `overflow-x: hidden`/`overflow-y: scroll` pada `html,body` memecah sticky/fixed di mobile → hindari; pakai `overflow-x: clip`.
- Urutan `openNavPanel()`: panggil `_updateNavTop()` SEBELUM set `body.style.top` (menggeser body mengubah getBoundingClientRect Header 2).
- Compiled blade di server bisa basi → selalu clear views tiap deploy blade.

## Catatan Git
- History commit TIDAK punya versi "awal" yang user maksud (versi real dengan bell/notifikasi tidak pernah di-commit). Jangan sandalkan git untuk rollback UI.

## Hukum Umum
- JANGAN commit secret/kredensial ke repo.
- User belum minta dibuat repo update; hanya commit bila diminta eksplisit.
- Ganti struktur menu/halaman admin hanya bila user minta; user pernah menegaskan "jangan ubah halaman admin" pada satu task spesifik (perbaikan halaman user).