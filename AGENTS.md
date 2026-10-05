# SIJAMAAH — Agent Working Notes

Proyek: Aplikasi presensi sholat berjamaah pondok pesantren (Laravel 10 + Tailwind + Blade).

## Environment
- Lokal: `C:\laragon\www\sijamaah-app` (Laragon, Windows, PowerShell 5.1)
- Live: `https://sijamaah.rf.gd` — `APP_NAME="SANTRI.ID"`. Hosting `ftpupload.net`, path `htdocs/`.
- **AKUN FTP YANG BENAR: `if0_42727909`** — kredensial lengkap ada di riwayat sesi opencode (jangan commit).
  **DILARANG memakai `if0_42850235`** — itu akun aplikasi lain (`APP_NAME="Sahara Kids Store"`, `saharastore.kesug.com`).
  Wajib verifikasi sebelum deploy:
  `curl.exe -sS --ftp-pasv -o - "ftp://ftpupload.net/htdocs/.env" --user <cred> | Select-String APP_NAME`
  Kalau hasilnya bukan `SANTRI.ID`, kamu meng-upload ke server yang salah dan perubahan tidak akan terlihat di situs.
- Build CSS aktif: `public\build\assets\app-fNfoOVRA.css` (jangan rebuild sembarangan).

## WAJIB BACA — Batasan File Build di Hosting (dibuktikan 5 Okt 2026)
Web server produksi (`openresty`) **hanya melayani file build yang sudah ada saat server start**.
- Upload `.css` dengan **nama baru** → `404` selamanya (diuji 3×: `app-64rT48x_.css`,
  `app-kontrol-baru.css`, `app-kontrol2.css` — semua 404 padahal isi & hash benar di FTP).
- **Overwrite** `app-fNfoOVRA.css` → upload sukses, tapi HTTP **masih serve isi lama**
  (99.313 byte) karena ada cache layer openresty/edge. Header respons:
  `Cache-Control: public, max-age=31536000, immutable`.
- `?v=<timestamp>` **tidak** membypass cache isi (tetap 99.313) — yang dia bypass hanya cache browser.
- `SITE CHMOD` ditolak server (550), jadi permission tidak bisa dirapikan lewat FTP.
- 6 CSS lama lain tertinggal di `public/build/assets/` (`app-B60Kc5GY`, `app-BjMfUfbt`,
  `app-CjcyRtN2`, `app-D7DVUzeM`, `app-i6901ZrK`, `app-fNfoOVRA`) — semuanya 404 kecuali `app-fNfoOVRA`.

**Konsekuensi:** jangan mengandalkan `npm run build` untuk deploy CSS. Kalau halaman baru butuh
class Tailwind yang belum ada di `app-fNfoOVRA.css`, dua pilihan:
1. **Tunggu cache host expire** (belum diketahui lamanya), atau
2. **Tulis CSS manual di `<style>` inline** di blade halaman itu (pola yang dipakai proyek ini
   selama ini, mis. `.input-kelas`, `.modal-kelas` di `views/kelas/index.blade.php`).
File PHP & blade TIDAK terpengaruh — keduanya dibaca via filesystem, bukan static handler.

Build lokal tetap jalan (node **22**, bukan 18 — Tailwind v4 butuh Node 20+):
`& "C:\laragon\bin\nodejs\node-v22\node.exe" "node_modules\vite\bin\vite.js" build`

## Alur Deploy ke Server
1. Compile blade via `_compile_test.php` sementara, lalu hapus.
2. **Cek akun dulu** (lihat Environment) — upload ke akun salah = perubahan tidak kelihatan.
3. Upload file: `curl.exe -sS --ftp-create-dirs -T <file_lokal> "ftp://ftpupload.net/htdocs/<path>" --user <user:pass>`.
3. Bersihkan compiled blade di server **lewat FTP** (BUKAN lewat `clear-views.php`):
   ```powershell
   $cred = "<user>:<pass>"
   $base = "ftp://ftpupload.net/htdocs/storage/framework/views/"
   $names = curl.exe -sS --ftp-pasv --list-only $base --user $cred | ? { $_ -and $_ -ne '.' -and $_ -ne '..' }
   $a = @('-sS','--ftp-pasv','ftp://ftpupload.net/','--user',$cred,'--quote','CWD /htdocs/storage/framework/views')
   foreach ($n in $names) { $a += @('--quote', "DELE $n") }
   curl.exe @a
   ```
   WAJIB ada `--quote "CWD <dir>"` di depan `DELE` — tanpa itu `DELE` dijalankan di root FTP dan selalu gagal `550 No such file`.
4. Verify: download ulang & cek hash IDENTICAL dengan lokal, lalu buka halamannya untuk memastikan masih 200.

### Kenapa `clear-views.php` tidak bisa dipakai
- Di server SANTRI.ID, file `.php` di root `htdocs/` tidak bisa dieksekusi lewat browser, jadi membersihkannya lewat URL selalu 404.
- `DELE` lewat FTP (langkah 4) deterministik dan terbukti berhasil.

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