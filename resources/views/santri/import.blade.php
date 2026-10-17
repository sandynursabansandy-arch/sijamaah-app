<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.pwa-head')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Santri dari CSV</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gradient-to-b from-slate-100 via-white to-emerald-50 text-slate-800 min-h-screen py-8">
    @include('partials.popup-global')
    <div class="max-w-2xl mx-auto px-4 sm:px-6">
        <!-- Header -->
        <div class="mb-5">
            <a href="{{ route('santri.index') }}" class="inline-flex items-center gap-1.5 text-slate-500 hover:text-slate-800 mb-3 text-xs font-medium">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Kembali ke Kelola Santri
            </a>
            <h1 class="text-xl sm:text-2xl md:text-[28px] leading-tight font-extrabold bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">
                Import Santri
            </h1>
            <p class="text-slate-500 text-xs">Tambah banyak santri sekaligus dari file CSV</p>
        </div>

        @if(session('success'))
            <script>glAlert('Berhasil', @json(session('success')), { jenis: 'sukses' });</script>
        @endif
        @if(session('error'))
            <script>glAlert('Gagal', @json(session('error')), { jenis: 'error' });</script>
        @endif

        <!-- Format -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-5">
            <h2 class="font-bold text-sm text-blue-800 mb-2">Format File CSV</h2>
            <p class="text-[13px] text-blue-700 mb-3">Unduh template di bawah ini, isi datanya di dalam kotak yang sudah bergaris, lalu langsung pilih file tersebut saat import &mdash; tidak perlu mengubah judul kolom.</p>
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <a href="{{ route('santri.import.template') }}"
                   class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white px-4 py-2.5 rounded-lg font-semibold text-sm shadow hover:opacity-90 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Download Template Excel
                </a>
                <a href="{{ route('santri.import.template', ['format' => 'csv']) }}"
                   class="inline-flex items-center gap-1.5 bg-white text-blue-700 border border-blue-200 px-3.5 py-2.5 rounded-lg font-semibold text-xs hover:bg-blue-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m-8 4h8m-8 4h5m-9 8h13a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Versi CSV polos
                </a>
            </div>
            <p class="text-[13px] text-blue-700 mb-2">Kolom (dengan atau tanpa baris header), pemisah koma <code class="bg-blue-100 px-1 rounded">,</code> atau titik-koma <code class="bg-blue-100 px-1 rounded">;</code>:</p>
            <pre class="text-[12px] bg-white border border-blue-100 rounded-lg p-3 overflow-x-auto leading-relaxed"><code>DATA SANTRI - FORMULIR IMPORT SANTRI.ID
Isi data pada baris di bawah. Kolom Nama Santri dan Rayon/Kamar wajib diisi.
Nama Santri,Rayon/Kamar,Kelas Pengajian,Jenjang Pendidikan,Jabatan,No. HP Wali,No. WhatsApp,Provinsi,Kabupaten/Kota,Kecamatan,Desa/Kelurahan,Dusun/Kampung,RT,RW,Jalan,No. Rumah,Kode Pos,Jenis Kelamin
Ahmad Fauzi,Rayon 1,1,7 SMP,Amir,081234567890,081234567890,Jawa Timur,Kota Pasuruan,Purmera,Desa X,Dusun 1,003,001,Jln. Merdeka,45,61252,Laki-laki</code></pre>
            <ul class="text-[12px] text-blue-700 mt-2 list-disc list-inside space-y-0.5">
                <li><strong>Nama Santri</strong> dan <strong>Rayon/Kamar</strong> wajib diisi</li>
                <li>Kolom <strong>Jenis Kelamin</strong> (kolom terakhir) opsional &mdash; isi <code class="bg-blue-100 px-1 rounded">Laki-laki</code> atau <code class="bg-blue-100 px-1 rounded">Perempuan</code> (singkatan <code class="bg-blue-100 px-1 rounded">L</code>/<code class="bg-blue-100 px-1 rounded">P</code>, <code class="bg-blue-100 px-1 rounded">Pria</code>, <code class="bg-blue-100 px-1 rounded">Wanita</code> juga dikenali)</li>
                <li>
                    <strong>Kelas Pengajian</strong> diisi untuk kelas majelis (mis. <code class="bg-blue-100 px-1 rounded">1</code>, <code class="bg-blue-100 px-1 rounded">Takhosus I</code>) &mdash;
                    tidak naik otomatis dan tidak semua Santri ikut naik
                </li>
                <li>
                    <strong>Jenjang Pendidikan</strong> diisi untuk kelas formal (mis. <code class="bg-blue-100 px-1 rounded">7 SMP</code>) &mdash;
                    naik satu tingkat tiap tahun ajar baru
                </li>
                <li>Kedua kolom kelas boleh diisi keduanya, boleh salah satu, boleh kosong</li>
                <li>File lama yang hanya punya satu kolom <strong>Kelas</strong> tetap bisa diimport &mdash; kolom itu dibaca sebagai pengajian atau sekolah sesuai jenisnya</li>
                <li>Kolom <strong>No. HP Wali</strong>, <strong>No. WhatsApp</strong>, dan kolom alamat opsional &mdash; biarkan kosong jika tidak ada</li>
                <li>Rayon baru akan dibuat otomatis jika belum ada</li>
                <li>Data duplikat (nama + rayon sama) akan dilewati</li>
                <li>Gunakan file template (Excel/CSV) agar judul kolom otomatis dikenali</li>
                <li>Baris judul dan baris keterangan boleh dihapus, judul kolom tetap dikenali</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('santri.import.store') }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 space-y-4"
              onsubmit="return glConfirmForm(this, 'Import Santri', 'Yakin ingin import santri dari file ini?', { ya: 'Ya, Import', warna: 'hijau', ikon: 'hijau' })">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase tracking-wide mb-1.5 text-slate-600">File CSV / Excel</label>
                <input type="file" name="file" accept=".csv,.xlsx,text/csv" required
                    class="w-full border border-slate-200 py-2 px-3 rounded-lg text-sm bg-slate-50 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-emerald-500 file:text-white file:text-xs file:font-semibold cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-300">
            </div>

            <button type="submit"
                class="w-full bg-gradient-to-r from-emerald-500 to-emerald-600 text-white py-2.5 rounded-lg font-semibold text-sm shadow-lg hover:opacity-90 transition">
                Import Sekarang
            </button>
        </form>
    </div>
</body>
</html>
