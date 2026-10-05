<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.pwa-head')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelas &amp; Tahun Ajar — SANTRI.ID</title>
    @vite(['resources/css/app.css'])
    <style>
        /* ------------------------------------------------------------------
            Utility Tailwind yang dipakai halaman ini tapi belum ada di build
            CSS yang ters-deploy. Server produksi hanya menyajikan file build
            yang sudah ada saat start dan meng-cache-nya immutable, jadi kelas
            baru tidak bisa lewat file CSS baru. Aturan di bawah diekstrak
            otomatis dari hasil `vite build` lokal (lihat _tmp_extract.cjs),
            jadi nilainya identik dengan Tailwind — bukan ditulis tangan.

            Rules ini sengaja TIDAK dibungkus @layer: <style> ini muncul
            setelah stylesheet, sehingga utility ini menang atas aturan dasar.
        ------------------------------------------------------------------ */
        @include('kelas._utility-tailwind')

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fadeIn 0.6s ease-in; }
        .animate-slide-up { animation: slideUp 0.6s ease-out; }
        .alert-animate { animation: slideUp 0.5s ease-out; }
        tbody tr { animation: fadeIn 0.3s ease-out both; }
        @for($i = 1; $i <= 20; $i++)
            tbody tr:nth-child({{ $i }}) { animation-delay: {{ $i * 0.04 }}s; }
        @endfor

        /* Modal */
        .modal-kelas[hidden] { display: none; }
        .modal-kelas {
            position: fixed; inset: 0; z-index: 10050;
            display: flex; align-items: flex-start; justify-content: center;
            padding: 1rem; overflow-y: auto;
        }
        .modal-kelas__overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(2px); }
        .modal-kelas__panel {
            position: relative; width: 100%; max-width: 30rem; margin: 3rem auto;
            background: #fff; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35);
            animation: slideUp 0.25s ease-out;
        }

        /* ------------------------------------------------------------------
     Panel kenaikan kelas pengajian per orang.
     Semua ditulis di sini (bukan utility Tailwind) karena file CSS build
     yang ters-deploy tidak bisa di-refresh — lihat AGENTS.md bagian
     "Batasan File Build di Hosting".
------------------------------------------------------------------ */
.kapasitas-badge {
    display: inline-block; min-width: 26px; padding: 2px 8px;
    border-radius: 6px; font-size: 12px; font-weight: 700;
    background: #eef2f7; color: #475569;
}
.kapasitas-hampir { background: #fef3c7; color: #92400e; }
.kapasitas-penuh  { background: #fee2e2; color: #b91c1c; }

.teks-naik {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 12px; font-weight: 600; color: #047857;
}
.teks-tautan {
    font-size: 11px; font-weight: 600; color: #047857; text-decoration: underline;
    background: none; border: 0; padding: 0; cursor: pointer;
}
.teks-tautan:hover { color: #065f46; }

.tombol-ok {
    padding: 9px 18px; border-radius: 10px; font-size: 13px; font-weight: 700;
    background: #059669; color: #fff; border: 0; cursor: pointer;
}
.tombol-ok:disabled { background: #cbd5e1; color: #94a3b8; cursor: not-allowed; }

.anggota-kotak {
    border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff;
}
.anggota-kepala {
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    padding: 8px 12px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;
    flex-wrap: wrap;
}
.anggota-daftar { max-height: 320px; overflow-y: auto; }
.anggota-item {
    display: flex; align-items: center; gap: 10px;
    padding: 7px 12px; border-bottom: 1px solid #f1f5f9; cursor: pointer;
}
.anggota-item:last-child { border-bottom: 0; }
.anggota-item:hover { background: #f0fdf4; }
.anggota-item input[type="checkbox"] { width: 16px; height: 16px; accent-color: #059669; flex: none; }
.anggota-nama { font-size: 13px; color: #1e293b; font-weight: 600; }
.anggota-meta { font-size: 11px; color: #94a3b8; }
.anggota-item[hidden] { display: none; }

/* Kartu tombol aksi */
        .kartu-aksi {
            display: flex; gap: 12px; align-items: flex-start;
            padding: 14px; border-radius: 14px; border: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        .kartu-aksi__ikon {
            flex: none; width: 38px; height: 38px; border-radius: 11px;
            display: flex; align-items: center; justify-content: center;
        }

        .label-kelas { display: block; font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 4px; }
        .input-kelas {
            width: 100%; border: 1px solid #e2e8f0; border-radius: 10px;
            padding: 9px 11px; font-size: 13px; color: #1e293b; background: #f8fafc;
        }
        .input-kelas:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.14); background: #fff; }

        .info-santri {
            margin-top: 10px; padding: 10px 12px; border-radius: 10px;
            background: #eef2ff; border: 1px solid #c7d2fe;
            font-size: 12px; color: #3730a3;
        }

        /* ------------------------------------------------------------------
            Tabel kelas — ukuran kolom yang aman di Android.

            Masalahnya: 7 kolom di dalam layar ponsel tidak mungkin muat, jadi
            tabel digeser horizontal. Sebelum diperbaiki, tabel dipaksa
            min-w:640px sementara lebar alami isinya lebih besar dari itu, dan
            browser membagi 640px itu ke semua kolom — kolom Kelas ikut tertekan
            sampai nama kelas terpotong di dalam inputnya.

            Perbaikannya: kolom Kelas dikuncilebarnya (min-width) dan kolom
            pertama dibuat Position:sticky supaya tetap terlihat saat tabel
            digeser. Aturan ditulis manual di sini, bukan utility Tailwind,
            karena file build CSS di server produksi tidak bisa diperbarui
            (lihat catatan di _utility-tailwind).
        ------------------------------------------------------------------ */
        .tabel-kelas-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior-x: contain;
        }
        .tabel-kelas { width: 100%; min-width: 600px; border-collapse: separate; border-spacing: 0; }
        .tabel-kelas th, .tabel-kelas td { padding: 8px 10px; vertical-align: middle; }
        .tabel-kelas thead th { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        .tabel-kelas tbody td { border-top: 1px solid #f1f5f9; }

        /* Kolom Kelas: lebar minimum + menempel di kiri, tidak pernah terpotong.
           Lebarnya hanya min-width, jadi di layar lebar kolom ini tetap ikut
           membesar bersama kolom lain. */
        .tabel-kelas .kol-kelas {
            position: sticky; left: 0; z-index: 1;
            min-width: 132px; background: #fff;
        }
        .tabel-kelas thead .kol-kelas { z-index: 2; background: #f8fafc; }
        .tabel-kelas .kol-kelas::after {
            content: ""; position: absolute; top: 0; right: 0; bottom: 0;
            width: 1px; background: #e2e8f0;
        }
        .input-kelas-nama { width: 100%; min-width: 0; max-width: 100%; }

        /* Kolom kecil: rapat supaya sisa ruang dipakai kolom Kelas. */
        .tabel-kelas .kol-aksi { width: 152px; }
        .tabel-kelas .aksi-grup { display: flex; flex-wrap: wrap; gap: 4px; justify-content: center; }
        .tabel-kelas .aksi { padding: 5px 8px; border-radius: 8px; font-size: 11px; font-weight: 600; }
        .tabel-kelas .jenis-grup { display: inline-flex; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
        .tabel-kelas .jenis-grup button { padding: 4px 6px; font-size: 11px; font-weight: 700; }

        /* Petunjuk geser tabel, hanya di layar sempit. */
        .hanya-ponsel { display: none; }
        @media (max-width: 640px) {
            .hanya-ponsel { display: flex; }
        }

        /* ------------------------------------------------------------------
            Layar popup (notifikasi & konfirmasi).

            confirm() bawaan browser tampilannya kecil di Android dan sering
            tidak kelihatan, jadi diganti popup yang sama dengan gaya modal
            halaman ini. Notifikasi hasil aksi juga pindah dari banner di
            dalam halaman ke popup supaya tidak ikut terpotong di ponsel.
        ------------------------------------------------------------------ */
        .popup-layar[hidden] { display: none; }
        .popup-layar {
            position: fixed; inset: 0; z-index: 10060;
            display: flex; align-items: center; justify-content: center;
            padding: 1rem; overflow-y: auto;
        }
        .popup-layar__overlay {
            position: fixed; inset: 0;
            background: rgba(15, 23, 42, 0.45);
            -webkit-backdrop-filter: blur(2px); backdrop-filter: blur(2px);
        }
        .popup-layar__panel {
            position: relative; width: 100%; max-width: 21rem; padding: 20px 18px 16px;
            background: #fff; border-radius: 16px; text-align: center;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35);
            animation: popupMuncul 0.22s cubic-bezier(0.21, 1.02, 0.73, 1);
        }
        @keyframes popupMuncul {
            from { opacity: 0; transform: translateY(14px) scale(0.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .popup-layar__ikon {
            width: 52px; height: 52px; margin: 0 auto 12px; border-radius: 9999px;
            display: flex; align-items: center; justify-content: center; flex: none;
        }
        .popup-layar__judul { font-size: 15px; font-weight: 700; color: #1e293b; line-height: 1.3; }
        .popup-layar__pesan {
            margin-top: 6px; font-size: 12.5px; line-height: 1.6; color: #64748b;
            white-space: pre-line; overflow-wrap: anywhere;
        }
        .popup-layar__aksi { margin-top: 16px; display: flex; gap: 8px; }
        .popup-layar__tombol {
            flex: 1 1 0; min-width: 0; padding: 10px 8px; border-radius: 10px;
            font-size: 13px; font-weight: 700; transition: background-color .15s ease;
        }
        .popup-ikon-hijau { background: #d1fae5; color: #059669; }
        .popup-ikon-merah { background: #fee2e2; color: #dc2626; }
        .popup-ikon-indigo { background: #e0e7ff; color: #4f46e5; }
        .popup-batal { background: #f1f5f9; color: #334155; }
        .popup-batal:active { background: #e2e8f0; }
        .popup-ya { background: #6366f1; color: #fff; }
        .popup-ya:active { background: #4f46e5; }
        .popup-ya.popup-ya-hijau { background: #10b981; }
        .popup-ya.popup-ya-hijau:active { background: #059669; }
        .popup-ya.popup-ya-merah { background: #ef4444; }
        .popup-ya.popup-ya-merah:active { background: #dc2626; }
    </style>
</head>
<body class="bg-gradient-to-b from-slate-100 via-slate-50 to-indigo-50 text-slate-800 min-h-screen pb-10">
    @include('partials.app-header')

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-5">

        <!-- Judul -->
        <div class="mb-4 flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center shadow-md">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16M8 12v6"/>
                </svg>
            </div>
            <div>
                <h1 class="text-[15px] font-bold text-slate-800 leading-tight">Kelas &amp; Tahun Ajar</h1>
                <p class="text-[11px] text-slate-400">Naik kelas sekali klik per kelas, bukan per Santri</p>
            </div>
        </div>

        {{-- Pesan hasil aksi (flash + validasi) dikumpulkan di sini, lalu
             ditampilkan satu per satu sebagai layar popup oleh JS di bawah.
             Sebelumnya berupa banner di dalam halaman yang ikut terpotong di
             ponsel. --}}
        @php
            $notifPopup = [];
            if (session('success')) {
                $notifPopup[] = ['jenis' => 'sukses', 'pesan' => session('success')];
            }
            if (session('error')) {
                $notifPopup[] = ['jenis' => 'gagal', 'pesan' => session('error')];
            }
            foreach ($errors->all() as $m) {
                $notifPopup[] = ['jenis' => 'gagal', 'pesan' => $m];
            }

            /* json_encode gagal mengembalikan false yang tercetak kosong, dan
               `var x = ;` membuat satu blok script gagal diparse — seluruh JS
               halaman ikut mati. Jadi di-encode di sini dan selalu dijamin
               JSON yang sah. */
            $notifJson = json_encode(
                $notifPopup,
                JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE
            );
            if ($notifJson === false) {
                $notifJson = '[]';
            }
        @endphp

        @if(!empty($belumDimigrasi))
            <div class="mb-4 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg text-sm alert-animate animate-fade-in leading-relaxed">
                <p class="font-bold mb-1">Tabel <code>kelas_master</code> belum ada di database server ini.</p>
                <p class="text-[12px]">
                    Fitur Naik Kelas belum aktif. Jalankan file
                    <code>deploy_kelas_master.sql</code> lewat phpMyAdmin (pilih database
                    <code>{{ config('database.connections.mysql.database') }}</code>), lalu muat ulang halaman ini.
                    Data Santri yang sudah ada tidak akan berubah.
                </p>
            </div>
        @endif

        <!-- Pilih tahun ajar -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 mb-4 flex flex-wrap items-center gap-3 animate-slide-up">
            <form method="GET" action="{{ route('kelas.index') }}" class="flex items-center gap-2">
                <label for="tahun_ajar" class="text-[11px] font-bold text-slate-500">Tahun Ajar</label>
                <select id="tahun_ajar" name="tahun_ajar" onchange="this.form.submit()"
                        class="border border-slate-200 rounded-lg px-3 py-1.5 text-[13px] bg-slate-50 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                    @foreach($daftarTahunAjar as $ta)
                        <option value="{{ $ta }}" @selected($ta === $tahunAjar)>{{ $ta }}</option>
                    @endforeach
                </select>
                <noscript><button type="submit" class="text-[12px] font-semibold text-indigo-600">Terapkan</button></noscript>
            </form>
            <div class="text-[12px] text-slate-500">
                <span class="font-semibold text-slate-700">{{ $totalSantri }}</span>Santri terdaftar pada {{ $tahunAjar }}
            </div>
            <div class="ml-auto flex flex-wrap gap-2">
                <button type="button" data-buka="modalNaik"
                        class="bg-indigo-500 text-white px-4 py-2 rounded-lg text-[12px] font-semibold hover:bg-indigo-600 transition shadow-sm inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                    Naik Kelas
                </button>
                <button type="button" data-buka="modalTahun"
                        class="bg-white text-indigo-600 border border-indigo-200 px-4 py-2 rounded-lg text-[12px] font-semibold hover:bg-indigo-50 transition shadow-sm inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M12 21a9 9 0 100-18 9 9 0 000 18z"/></svg>
                    Buka Tahun Ajar Baru
                </button>
            </div>
        </div>

        <!-- Penjelasan -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4 animate-slide-up" style="animation-delay:0.05s">
            <div class="kartu-aksi">
                <div class="kartu-aksi__ikon bg-indigo-100 text-indigo-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[13px] font-bold text-slate-800 leading-tight">Naik Kelas (per kelas)</p>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                        Semua Santri satu kelas dipindah sekaligus ke kelas tujuan.
                        Cukup <strong>1 klik per kelas per tahun</strong> — 1.500 Santri di 7 kelas jadi 7 klik, bukan 1.500.
                    </p>
                </div>
            </div>
            <div class="kartu-aksi">
                <div class="kartu-aksi__ikon bg-emerald-100 text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M12 21a9 9 0 100-18 9 9 0 000 18z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[13px] font-bold text-slate-800 leading-tight">Buka Tahun Ajar Baru</p>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                        Master kelas tahun lalu disalin dengan tingkat naik sendiri
                        (7 SMP → 8 SMP). Semua Santri pindah dalam satu transaksi — sekali klik per tahun.
                    </p>
                </div>
            </div>
        </div>

        <!-- Tambah kelas -->
        <div class="bg-white rounded-xl shadow-lg p-5 mb-4 animate-slide-up" style="animation-delay:0.1s">
            <h2 class="text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-3">Tambah Kelas di {{ $tahunAjar }}</h2>
            <form method="POST" action="{{ route('kelas.store') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                @csrf
                <input type="hidden" name="tahun_ajar" value="{{ $tahunAjar }}">
                <div class="sm:col-span-2">
                    <label class="label-kelas" for="nama_kelas_baru">Nama Kelas</label>
                    <input type="text" id="nama_kelas_baru" name="nama" required maxlength="100"
                           placeholder="ex: 1, 2, Takhosus I, 7 SMP, 10 SMA"
                           class="input-kelas">
                </div>
                <div>
                    <label class="label-kelas" for="jenis_kelas_baru">Jenis</label>
                    <select id="jenis_kelas_baru" name="jenis" class="input-kelas">
                        <option value="">Otomatis (dari nama)</option>
                        <option value="pengajian">Pengajian — tidak naik otomatis</option>
                        <option value="formal">Sekolah — naik tiap tahun</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-slate-700 text-white px-4 py-2 rounded-lg text-[12px] font-semibold hover:bg-slate-800 transition shadow-sm">
                        + Tambah Kelas
                    </button>
                </div>
                <div class="sm:col-span-4 text-[11px] text-slate-400">
                    <strong class="text-slate-600">Pengajian</strong> = kelas majelis (Kelas 1&ndash;6, Takhosus I/II):
                    <strong>tidak naik otomatis</strong>, dan tidak semua Santri di dalamnya naik —
                    pilih per kelas lewat tombol Naik. <strong class="text-slate-600">Sekolah</strong> = kelas formal
                    (7 SMP, 10 SMA) yang naik satu tingkat tiap tahun. Jenis otomatis dibaca dari nama —
                    nama yang mengandung jenjang dianggap sekolah, sisanya pengajian — dan bisa diubah
                    lewat toggle di tabel.
                </div>
            </form>
        </div>

<!-- ========== TABEL 1: KELAS PENGAJIAN ========== -->
        <div class="bg-white rounded-xl shadow-sm border border-emerald-100 overflow-hidden animate-slide-up" style="animation-delay:0.15s">
            <div class="px-4 py-3 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-[12px] font-bold uppercase tracking-wide text-emerald-700">Kelas Pengajian</h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                        {{ $jumlahPengajian }} orang dari {{ $kapasitasPengajian }} kursi
                        @if($kelasPengajian->isNotEmpty())
                            &mdash; {{ $kelasPengajian->count() }} kelas, tidak naik otomatis
                        @endif
                    </p>
                </div>
                <a href="{{ route('santri.index', ['tahun_ajar' => $tahunAjar]) }}"
                   class="text-[12px] font-semibold text-indigo-600 hover:text-indigo-700 whitespace-nowrap">Lihat Santri &rarr;</a>
            </div>

            <div class="px-4 py-2 bg-emerald-50 border-b border-emerald-100 text-[11px] text-emerald-800 leading-relaxed">
                Kelas pengajian tidak mengikuti kenaikan tahun ajaran. Seseorang <strong>hanya naik</strong>
                kalau benar-benar paham dan layak naik &mdash; itu keputusan guru, bukan aturan sistem.
                Target tiap kelas sekitar <strong>20 orang</strong>; lewat dari itu berarti ada yang belum naik.
                Naikkan per orang lewat panel <strong>Kenaikan Kelas Pengajian</strong> di bawah.
            </div>

            @if($kelasPengajian->isEmpty())
                <div class="px-6 py-12 text-center">
                    <p class="text-sm font-bold text-slate-700">Belum ada kelas pengajian</p>
                    <p class="text-xs text-slate-400 mt-1">Tambahkan lewat form di atas, mis. Kelas 1 atau Takhosus I.</p>
                </div>
            @else
                <div class="tabel-kelas-wrap">
                    <table class="tabel-kelas">
                        <thead class="text-slate-500 text-[11px] font-bold uppercase tracking-wide">
                            <tr>
                                <th class="kol-kelas text-left">Kelas Pengajian</th>
                                <th class="text-left">Isi</th>
                                <th class="text-center">Target</th>
                                <th class="kol-aksi text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-[13px]">
                            @foreach($kelasPengajian as $k)
                                @php
                                    $isi = (int) $k->santris_pengajian_count;
                                    $target = $k->kapasitasEfektif();
                                    $sisa = $k->sisaKursi();
                                @endphp
                                <tr>
                                    <td class="kol-kelas">
                                        <input type="text" form="formKelas{{ $k->id }}" name="nama" value="{{ $k->nama }}" maxlength="100" size="1"
                                               class="input-kelas input-kelas-nama font-semibold">
                                    </td>
                                    <td>
                                        <span class="kapasitas-badge {{ $sisa !== null && $sisa <= 0 ? 'kapasitas-penuh' : ($sisa !== null && $sisa <= 3 ? 'kapasitas-hampir' : '') }}">{{ $isi }}{{ $target ? ' / ' . $target : '' }}</span>
                                        @if($sisa !== null && $sisa <= 0)
                                            <span class="text-[10px] text-amber-600 ml-1">penuh</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <input type="number" form="formKelas{{ $k->id }}" name="kapasitas"
                                               value="{{ $k->kapasitas }}" min="1" max="500" placeholder="20"
                                               class="input-kelas w-[70px] text-center">
                                    </td>
                                    <td class="kol-aksi">
                                        <div class="aksi-grup">
                                            <button type="submit" form="formKelas{{ $k->id }}"
                                                    class="aksi bg-slate-100 text-slate-700 hover:bg-slate-200 transition">Simpan</button>
                                            <button type="button" class="aksi bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition"
                                                    onclick="isiNaikPengajian('{{ $k->id }}')">Naik</button>
                                            <button type="submit" form="formHapus{{ $k->id }}"
                                                    class="aksi bg-red-50 text-red-600 hover:bg-red-100 transition">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- ========== TABEL 2: KELAS SEKOLAH ========== -->
        <div class="bg-white rounded-xl shadow-sm border border-indigo-100 overflow-hidden animate-slide-up mt-4" style="animation-delay:0.2s">
            <div class="px-4 py-3 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-[12px] font-bold uppercase tracking-wide text-indigo-700">Kelas Sekolah</h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">{{ $jumlahSekolah }} orang &mdash; naik satu tingkat tiap tahun ajaran</p>
                </div>
                <a href="{{ route('santri.index', ['tahun_ajar' => $tahunAjar]) }}"
                   class="text-[12px] font-semibold text-indigo-600 hover:text-indigo-700 whitespace-nowrap">Lihat Santri &rarr;</a>
            </div>

            <div class="px-4 py-2 bg-indigo-50 border-b border-indigo-100 text-[11px] text-indigo-800 leading-relaxed">
                Kelas sekolah mengikuti jenjang resmi, jadi <strong>naik otomatis</strong> saat tahun ajaran baru
                dibuka (7 SMP &rarr; 8 SMP). Kelas pengajian terpisah dan tidak tersentuh proses ini.
            </div>

            @if($kelasSekolah->isEmpty())
                <div class="px-6 py-12 text-center">
                    <p class="text-sm font-bold text-slate-700">Belum ada kelas sekolah</p>
                    <p class="text-xs text-slate-400 mt-1">Tambahkan lewat form di atas, mis. 7 SMP atau 2 SMA.</p>
                </div>
            @else
                <div class="tabel-kelas-wrap">
                    <table class="tabel-kelas">
                        <thead class="text-slate-500 text-[11px] font-bold uppercase tracking-wide">
                            <tr>
                                <th class="kol-kelas text-left">Kelas Sekolah</th>
                                <th class="text-left">Jenis</th>
                                <th class="text-left">Jenjang</th>
                                <th class="text-center">Tingkat</th>
                                <th class="text-center">Santri</th>
                                <th class="text-left">Kelas Berikutnya</th>
                                <th class="kol-aksi text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-[13px]">
                            @foreach($kelasSekolah as $k)
                                <tr>
                                    <td class="kol-kelas">
                                        <input type="text" form="formKelas{{ $k->id }}" name="nama" value="{{ $k->nama }}" maxlength="100" size="1"
                                               class="input-kelas input-kelas-nama font-semibold">
                                    </td>
                                    <td>@include('kelas._toggle-jenis', ['k' => $k])</td>
                                    <td class="text-slate-600">{{ $k->jenjang ?: '-' }}@if($k->sisa)<span class="text-slate-400">{{ $k->sisa }}</span>@endif</td>
                                    <td class="text-center text-slate-600">{{ $k->tingkat ?? '-' }}</td>
                                    <td class="text-center"><span class="kapasitas-badge">{{ (int) $k->santris_sekolah_count }}</span></td>
                                    <td>
                                        @php $saran = $saranKelasBerikut[$k->id] ?? null; @endphp
                                        @if($saran)
                                            <span class="teks-naik">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7l-4 5 4 5M16 7l4 5-4 5"/></svg>
                                                {{ $saran }}
                                            </span>
                                        @else
                                            <span class="text-[11px] text-slate-400">{{ $k->tingkat === null ? 'tingkat manual - isi sendiri' : 'tingkat tertinggi' }}</span>
                                        @endif
                                    </td>
                                    <td class="kol-aksi">
                                        <div class="aksi-grup">
                                            <button type="submit" form="formKelas{{ $k->id }}"
                                                    class="aksi bg-slate-100 text-slate-700 hover:bg-slate-200 transition">Simpan</button>
                                            <button type="button" class="aksi bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition"
                                                    onclick="isiNaik('{{ $k->id }}')">Naik</button>
                                            <button type="submit" form="formHapus{{ $k->id }}"
                                                    class="aksi bg-red-50 text-red-600 hover:bg-red-100 transition">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- ========== PANEL: KENAIKAN KELAS PENGAJIAN (per orang) ========== -->
        <div id="panelPengajian" class="bg-white rounded-xl shadow-sm border border-emerald-200 overflow-hidden animate-slide-up mt-4" style="animation-delay:0.25s">
            <div class="px-4 py-3 border-b border-emerald-100 bg-emerald-50">
                <h2 class="text-[12px] font-bold uppercase tracking-wide text-emerald-700">Kenaikan Kelas Pengajian</h2>
                <p class="text-[11px] text-emerald-800 mt-1 leading-relaxed">
                    Naik <strong>per orang</strong>, bukan per kelas. Centang yang sudah layak naik; yang tidak
                    dicentang <strong>tetap</strong> di kelasnya. Kelas sekolah mereka tidak ikut berubah.
                </p>
            </div>

            @if($kelasPengajian->count() < 2)
                <div class="px-6 py-10 text-center">
                    <p class="text-sm font-bold text-slate-700">Perlu minimal 2 kelas pengajian</p>
                    <p class="text-xs text-slate-400 mt-1">
                        Panel ini memindahkan orang dari satu kelas pengajian ke kelas pengajian lain.
                        Tambahkan kelas pengajian kedua dulu.
                    </p>
                </div>
            @else
                <form method="POST" action="{{ route('kelas.naik-pengajian') }}" class="px-4 py-4 space-y-3">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="label-kelas" for="naikDariPengajian">Kelas Asal</label>
                            <select id="naikDariPengajian" class="input-kelas" onchange="muatAnggotaPengajian()">
                                @foreach($kelasPengajian as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama }} ({{ (int) $k->santris_pengajian_count }} orang)</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label-kelas" for="naikKePengajian">Kelas Tujuan</label>
                            <select id="naikKePengajian" class="input-kelas" required>
                                <option value="">- pilih kelas tujuan -</option>
                                @foreach($kelasPengajian as $k)
                                    <option value="{{ $k->id }}" data-nama="{{ $k->nama }}" data-sisa="{{ $k->sisaKursi() }}">
                                        {{ $k->nama }} ({{ (int) $k->santris_pengajian_count }} orang)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="anggota-kotak">
                        <div class="anggota-kepala">
                            <span class="text-[11px] font-bold text-slate-600" id="jumlahTerpilih">0 dipilih</span>
                            <div class="flex items-center gap-2">
                                <input type="search" id="cariAnggota" placeholder="Cari nama..."
                                       class="input-kelas w-[150px]" oninput="saringAnggota()">
                                <button type="button" onclick="pilihSemuaAnggota(true)" class="teks-tautan">Pilih semua</button>
                                <button type="button" onclick="pilihSemuaAnggota(false)" class="teks-tautan">Kosongkan</button>
                            </div>
                        </div>

                        <div id="daftarAnggota" class="anggota-daftar">
                            <p class="px-3 py-6 text-center text-[12px] text-slate-400">Memuat anggota...</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-[11px] text-slate-500 leading-relaxed" style="max-width:34rem;">
                            Kalau kelas tujuan sudah lewat target, peningkatannya tetap jalan &mdash; itu tandanya
                            masih ada yang belum layak naik, dan pesannya akan mengatakannya.
                        </p>
                        <button type="submit" id="tombolNaikPengajian" disabled class="tombol-ok">Naikkan yang dipilih</button>
                    </div>
                </form>
            @endif
        </div>

        {{-- Form edit & hapus disimpan di luar tabel agar valid HTML; tombol di
             baris tabel mereferensikannya lewat atribut form=. Konfirmasi hapus
             di-delegate ke onsubmit form ini, jadi tetap muncul baik dari tombol
             Hapus maupun dari tombol Enter di dalam kolom nama. --}}
        <div class="hidden">
            @foreach($kelas as $k)
                <form id="formKelas{{ $k->id }}" method="POST" action="{{ route('kelas.update', $k) }}">
                    @csrf
                    @method('PUT')
                </form>
                <form id="formHapus{{ $k->id }}" method="POST" action="{{ route('kelas.destroy', $k) }}"
                      data-nama-kelas="{{ $k->nama }}"
                      onsubmit="return konfirmasiHapus(this)">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach
        </div>

        <p class="text-[11px] text-slate-400 mt-4 leading-relaxed">
            Nama, NIS, kamar, alamat, dan WhatsApp tidak tersentuh saat naik kelas. Naik kelas
            hanya memengaruhi <strong>satu jalur</strong> — memindahkan kelas pengajian tidak mengubah
            kelas sekolah Santri yang sama, dan sebaliknya. Untuk Santri yang tinggal kelas,
            pindah mondok, atau keluar, ubah dari halaman
            <a href="{{ route('santri.index') }}" class="font-semibold text-indigo-600">Kelola Santri</a>.
        </p>
    </div>

    <!-- ===== MODAL: Naik Kelas ===== -->
    <div id="modalNaik" class="modal-kelas" hidden>
        <div class="modal-kelas__overlay" data-tutup></div>
        <div class="modal-kelas__panel" role="dialog" aria-modal="true" aria-labelledby="judulNaik">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 id="judulNaik" class="text-[14px] font-bold text-slate-800">Naik Kelas</h3>
                <button type="button" data-tutup class="w-7 h-7 rounded-lg hover:bg-slate-100 text-slate-400 text-lg leading-none">&times;</button>
            </div>

            <form method="POST" action="{{ route('kelas.naik') }}" class="px-5 py-4 space-y-3" id="formNaik">
                @csrf
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    Semua Santri pada kelas asal langsung dipindah ke kelas tujuan, sekaligus
                    kolom teks <em>Kelas Pengajian</em> atau <em>Kelas Sekolah</em>-nya ikut menyesuaikan
                    supaya cetak/Excel/FDF tetap benar. Kelas tujuan hanya boleh sekelas sejenis.
                </p>

                <div>
                    <label class="label-kelas" for="dari_kelas_id">Kelas Asal</label>
                    <select id="dari_kelas_id" name="dari_kelas_id" required class="input-kelas" onchange="pratinjauNaik()">
                        <option value="">— pilih kelas asal —</option>
                        @foreach($daftarTahunAjar as $ta)
                            @php $kl = $kelasPerTahunAjar->get($ta, collect()); @endphp
                            @if($kl->isNotEmpty())
                                <optgroup label="{{ $ta }}">
                                    @foreach($kl as $o)
                                        <option value="{{ $o->id }}" data-nama="{{ $o->nama }}" data-ta="{{ $o->tahun_ajar }}" data-jenis="{{ $o->isPengajian() ? 'pengajian' : 'formal' }}">{{ $o->nama }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-center -my-1">
                    <span class="w-7 h-7 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </span>
                </div>

                <div>
                    <label class="label-kelas" for="ke_kelas_id">Kelas Tujuan</label>
                    <select id="ke_kelas_id" name="ke_kelas_id" required class="input-kelas">
                        <option value="">— pilih kelas tujuan —</option>
                        @foreach($daftarTahunAjar as $ta)
                            @php $kl = $kelasPerTahunAjar->get($ta, collect()); @endphp
                            @if($kl->isNotEmpty())
                                <optgroup label="{{ $ta }}">
                                    @foreach($kl as $o)
                                        <option value="{{ $o->id }}" data-nama="{{ $o->nama }}" data-ta="{{ $o->tahun_ajar }}" data-jenis="{{ $o->isPengajian() ? 'pengajian' : 'formal' }}">{{ $o->nama }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div id="infoNaik" class="info-santri" hidden></div>

                <div class="pt-1 flex items-center gap-2">
                    <button type="submit" id="tombolNaik"
                            class="flex-1 bg-indigo-500 text-white px-4 py-2.5 rounded-lg text-[13px] font-semibold hover:bg-indigo-600 transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        Pindahkan Santri
                    </button>
                    <button type="button" data-tutup class="px-4 py-2.5 rounded-lg text-[13px] font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL: Buka Tahun Ajar Baru ===== -->
    <div id="modalTahun" class="modal-kelas" hidden>
        <div class="modal-kelas__overlay" data-tutup></div>
        <div class="modal-kelas__panel" role="dialog" aria-modal="true" aria-labelledby="judulTahun">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 id="judulTahun" class="text-[14px] font-bold text-slate-800">Buka Tahun Ajar Baru</h3>
                <button type="button" data-tutup class="w-7 h-7 rounded-lg hover:bg-slate-100 text-slate-400 text-lg leading-none">&times;</button>
            </div>

            <form method="POST" action="{{ route('kelas.buka-tahun-ajar') }}" id="formTahun"
                  class="px-5 py-4 space-y-3" onsubmit="return konfirmasiTahun(this)">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label-kelas" for="dari_tahun_ajar">Dari Tahun Ajar</label>
                        <select id="dari_tahun_ajar" name="dari_tahun_ajar" required class="input-kelas">
                            @foreach($daftarTahunAjar as $ta)
                                <option value="{{ $ta }}" @selected($ta === $tahunAjar)>{{ $ta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label-kelas" for="ke_tahun_ajar">Ke Tahun Ajar</label>
                        <input type="text" id="ke_tahun_ajar" name="ke_tahun_ajar" required
                               value="{{ $tahunAjarBerikut }}" placeholder="2027/2028" class="input-kelas">
                    </div>
                </div>

                <label class="flex items-start gap-2.5 p-3 rounded-xl border border-emerald-200 bg-emerald-50 cursor-pointer">
                    <input type="checkbox" name="pindahkan_santri" value="1" checked
                           class="mt-0.5 w-4 h-4 accent-emerald-600">
                    <span class="text-[12px] text-emerald-800 leading-relaxed">
                        <strong>Pindahkan otomatis kelas sekolah</strong> ke tahun ajar baru.
                        Kelas <strong>sekolah</strong> naik satu tingkat tiap tahun.
                        Kelas <strong>pengajian tetap di kelasnya</strong> (tidak naik kelas) &mdash;
                        pilih per kelas lewat tombol Naik, karena tidak semua orangnya naik.
                    </span>
                </label>

                {{-- Pratinjau apa yang terjadi ke tiap kelas, langsung dari data
                     tahun ajar asal — supaya tidak ada kejutan setelah klik. --}}
                @php
                    $jmlPengajian = $kelas->filter->isPengajian()->count();
                    $jmlFormal = $kelas->count() - $jmlPengajian;
                @endphp
                @if($kelas->isNotEmpty())
                    <div class="border border-slate-200 rounded-lg overflow-hidden">
                        <div class="px-3 py-2 bg-slate-50 text-[11px] font-bold uppercase tracking-wide text-slate-500">
                            Pratinjau {{ $tahunAjar }} → {{ $tahunAjarBerikut }}
                        </div>
                        <div class="px-3 py-2 text-[12px] leading-relaxed space-y-1">
                            @if($jmlPengajian > 0)
                                <p class="text-amber-700">
                                    <strong>{{ $jmlPengajian }} kelas pengajian</strong> disalin dengan nama sama
                                    dan <strong>Santri-nya TETAP di kelas itu</strong> &mdash; tidak naik kelas.
                                    Kalau yang naik, pilih per kelas lewat tombol Naik:
                                    @foreach($kelas->filter->isPengajian()->pluck('nama') as $nama)
                                        <span class="text-amber-600">{{ $nama }}</span>@if(! $loop->last),@endif
                                    @endforeach
                                </p>
                            @endif
                            @if($jmlFormal > 0)
                                <p class="text-indigo-700">
                                    <strong>{{ $jmlFormal }} kelas sekolah</strong> naik satu tingkat
                                    (Santri ikut pindah):
                                    @foreach($kelas->reject->isPengajian() as $k)
                                        <span class="text-indigo-600">{{ $k->nama }} → {{ $k->namaKelasBerikutnya() ?? $k->nama }}</span>@if(! $loop->last),@endif
                                    @endforeach
                                </p>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="text-[11px] text-slate-500 leading-relaxed bg-slate-50 border border-slate-200 rounded-lg p-3">
<strong>Kelas pengajian tidak naik otomatis</strong> &mdash; Kelas 1&ndash;6 dan Takhosus I/II
                        disalin dengan nama yang sama, dan <strong>Santri-nya tetap di kelas itu</strong>.
                        Di pengajian tidak semua orang naik, jadi naik/tidaknya tiap orang diputuskan guru:
                        setelah tahun ajar dibuat, pindahkan yang memang naik lewat tombol
                        <strong>Naik</strong> di tabel.
                    Kelas sekolah naik seperti biasa: 7 SMP → 8 SMP, 8 SMP → 9 SMP, Ibtida 1 → Ibtida 2.
                    Kelas yang sudah tingkat tertinggi (mis. 12 SMA) disalin dengan nama sama dan perlu Anda sunting manual.
                    Tahun ajar lama otomatis ditandai tidak aktif.
                </div>

                <div class="pt-1 flex items-center gap-2">
                    <button type="submit"
                            class="flex-1 bg-emerald-500 text-white px-4 py-2.5 rounded-lg text-[13px] font-semibold hover:bg-emerald-600 transition shadow-sm">
                        Buat Tahun Ajar
                    </button>
                    <button type="button" data-tutup class="px-4 py-2.5 rounded-lg text-[13px] font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== LAYAR POPUP: Notifikasi ===== -->
    {{-- Pesan hasil aksi (flash / validasi) muncul di sini sebagai popup,
         bukan banner di dalam halaman. Isinya diisi oleh JS dari $notifPopup. --}}
    <div id="popupNotif" class="popup-layar" hidden>
        <div class="popup-layar__overlay" data-tutup-notif></div>
        <div class="popup-layar__panel" role="alertdialog" aria-modal="true" aria-labelledby="popupNotifJudul" aria-describedby="popupNotifPesan">
            <div id="popupNotifIkon" class="popup-layar__ikon popup-ikon-hijau">
                <svg id="popupNotifIkonSukses" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <svg id="popupNotifIkonGagal" class="w-6 h-6 hidden" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <h3 id="popupNotifJudul" class="popup-layar__judul">Berhasil</h3>
            <p id="popupNotifPesan" class="popup-layar__pesan"></p>
            <div class="popup-layar__aksi">
                <button type="button" id="popupNotifOk" class="popup-layar__tombol popup-ya popup-ya-hijau" data-tutup-notif>Mengerti</button>
            </div>
        </div>
    </div>

    <!-- ===== LAYAR POPUP: Konfirmasi ===== -->
    {{-- Menggantikan window.confirm() yang tampilannya kecil dan mudah
         terlewat di Android. --}}
    <div id="popupKonfirmasi" class="popup-layar" hidden>
        <div class="popup-layar__overlay" data-tutup-konfirmasi></div>
        <div class="popup-layar__panel" role="dialog" aria-modal="true" aria-labelledby="popupKonfirmasiJudul" aria-describedby="popupKonfirmasiPesan">
            <div id="popupKonfirmasiIkon" class="popup-layar__ikon popup-ikon-indigo">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <h3 id="popupKonfirmasiJudul" class="popup-layar__judul"></h3>
            <p id="popupKonfirmasiPesan" class="popup-layar__pesan"></p>
            <div class="popup-layar__aksi">
                <button type="button" class="popup-layar__tombol popup-batal" data-tutup-konfirmasi>Batal</button>
                <button type="button" id="popupKonfirmasiYa" class="popup-layar__tombol popup-ya">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>

<script>
        (function () {
            /* ============================================================
               LAYAR & MODAL

               Satu pasang fungsi untuk buka/tutup semua lapisan (modal Naik
               Kelas, modal Buka Tahun Ajar, dan popup baru di bawah), supaya
               kunci gulir halaman tidak saling menimpa: menutup satu lapisan
               tidak membuka gulir padahal lapisan lain masih terbuka.
               ============================================================ */
            function syncGulir() {
                var ada = document.querySelector('.modal-kelas:not([hidden]), .popup-layar:not([hidden])');
                document.body.style.overflow = ada ? 'hidden' : '';
            }

            function buka(id) {
                var el = document.getElementById(id);
                if (!el) return;
                el.hidden = false;
                syncGulir();
                var f = el.querySelector('select, input:not([type=checkbox]), button');
                if (f) setTimeout(function () { f.focus(); }, 40);
            }

            function tutup(el) {
                el.hidden = true;
                syncGulir();
            }

            document.querySelectorAll('[data-buka]').forEach(function (b) {
                b.addEventListener('click', function () { buka(b.getAttribute('data-buka')); });
            });
            document.querySelectorAll('.modal-kelas [data-tutup]').forEach(function (b) {
                b.addEventListener('click', function () { tutup(b.closest('.modal-kelas')); });
            });

            window.isiNaik = function (kelasId) {
                buka('modalNaik');
                var asal = document.getElementById('dari_kelas_id');
                asal.value = kelasId;
                pratinjauNaik();
            };

            /* ============================================================
               KENAIKAN KELAS PENGAJIAN — per orang, bukan per kelas.

               Daftar anggota diambil dari /kelas/{id}/anggota supaya halaman
               tidak perlu memuat semua roster sekaligus. Kelas tujuan yang
               penuh tidak diblokir: di pengajian memang boleh lebih dari 20
               orang, itu tandanya masih ada yang belum layak naik.
               ============================================================ */
            var selectDari = document.getElementById('naikDariPengajian');
            var selectKe = document.getElementById('naikKePengajian');
            var kotakAnggota = document.getElementById('daftarAnggota');
            var jumlahDipilih = document.getElementById('jumlahTerpilih');
            var tombolNaik = document.getElementById('tombolNaikPengajian');

            function hitungTerpilih() {
                var n = kotakAnggota.querySelectorAll('input[type="checkbox"]:checked').length;
                jumlahDipilih.textContent = n + ' dipilih';

                if (tombolNaik) {
                    // Tidak cukup hanya "ada yang dicentang": kelas tujuan juga
                    // harus sudah dipilih, kalau tidak tombolnya menipu.
                    tombolNaik.disabled = n === 0 || !selectKe.value;
                }
            }

            window.saringAnggota = function () {
                var cari = (document.getElementById('cariAnggota').value || '').toLowerCase().trim();

                kotakAnggota.querySelectorAll('.anggota-item').forEach(function (item) {
                    var nama = (item.dataset.nama || '').toLowerCase();
                    item.hidden = cari !== '' && nama.indexOf(cari) === -1;
                });
            };

            window.pilihSemuaAnggota = function (centang) {
                // Hanya yang terlihat setelah pencarian yang ikut berubah,
                // supaya "pilih semua" tidak mencentang orang yang tersembunyi.
                kotakAnggota.querySelectorAll('.anggota-item').forEach(function (item) {
                    if (item.hidden) return;
                    item.querySelector('input[type="checkbox"]').checked = centang;
                });

                hitungTerpilih();
            };

            window.muatAnggotaPengajian = function () {
                var id = selectDari.value;
                hitungTerpilih();

                if (!id) return;

                // Kelas asal tidak boleh jadi kelas tujuan.
                Array.prototype.forEach.call(selectKe.options, function (o) {
                    if (!o.value) return;
                    o.disabled = o.value === id;
                    o.hidden = o.value === id;
                });

                kotakAnggota.innerHTML = '<p class="px-3 py-6 text-center text-[12px] text-slate-400">Memuat anggota...</p>';

                fetch(@json(route('kelas.anggota', ['kelas' => 0])).replace('/0/', '/' + id + '/'), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function (r) { return r.json(); })
                    .then(function (d) {
                        if (!d.anggota || !d.anggota.length) {
                            kotakAnggota.innerHTML = '<p class="px-3 py-6 text-center text-[12px] text-slate-400">'
                                + (d.pesan || 'Kelas pengajian ini belum punya anggota.') + '</p>';
                            return;
                        }

                        var html = d.anggota.map(function (a) {
                            var meta = [a.kamar, a.kelas_sekolah ? 'sekolah: ' + a.kelas_sekolah : null]
                                .filter(Boolean).join(' · ');

                            return '<label class="anggota-item" data-nama="' + a.nama.replace(/"/g, '&quot;') + '">'
                                + '<input type="checkbox" name="santri_ids[]" value="' + a.id + '" onchange="hitungTerpilih()">'
                                + '<span><span class="anggota-nama">' + a.nama + '</span>'
                                + (meta ? '<br><span class="anggota-meta">' + meta + '</span>' : '')
                                + '</span></label>';
                        }).join('');

                        kotakAnggota.innerHTML = html;

                        var ket = '<div id="ketAnggota"><p class="px-3 py-2 bg-slate-50 border-t border-slate-100 text-[11px] text-slate-500">'
                            + d.jumlah + ' orang' + (d.kapasitas ? ' dari target ' + d.kapasitas : '')
                            + ' &mdash; yang tidak dicentang tetap di ' + d.kelas + '.</p></div>';

                        var lama = document.getElementById('ketAnggota');
                        if (lama) {
                            lama.outerHTML = ket;
                        } else {
                            kotakAnggota.insertAdjacentHTML('afterend', ket);
                        }

                        hitungTerpilih();
                    })
                    .catch(function () {
                        kotakAnggota.innerHTML = '<p class="px-3 py-6 text-center text-[12px] text-red-500">Gagal memuat anggota. Coba muat ulang halaman.</p>';
                    });
            };

            window.isiNaikPengajian = function (kelasId) {
                var panel = document.getElementById('panelPengajian');
                panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                selectDari.value = kelasId;
                window.muatAnggotaPengajian();
            };

            if (selectDari) {
                selectKe.addEventListener('change', hitungTerpilih);
                window.muatAnggotaPengajian();
            }

            /* ============================================================
               LAYAR POPUP — dua hal yang tadinya "muncul" tanpa popup:
               1. Pesan hasil aksi (flash + validasi) — tadinya banner di
                  dalam halaman, jadi ikut terpotong di layar ponsel.
               2. Konfirmasi Hapus Kelas & Buat Tahun Ajar — tadinya
                  window.confirm() bawaan browser, yang di Android kecil
                  sekali dan sering tidak terlihat.
               ============================================================ */
            var antrianNotif = {!! $notifJson !!};
            var notifAktif = false;
            var timerNotif = null;

            function tampilkanNotifBerikutnya() {
                if (notifAktif || !antrianNotif.length) return;
                notifAktif = true;

                var item = antrianNotif.shift();
                var sukses = item.jenis !== 'gagal';

                document.getElementById('popupNotifIkon').className =
                    'popup-layar__ikon ' + (sukses ? 'popup-ikon-hijau' : 'popup-ikon-merah');
                document.getElementById('popupNotifIkonSukses').classList.toggle('hidden', !sukses);
                document.getElementById('popupNotifIkonGagal').classList.toggle('hidden', sukses);

                document.getElementById('popupNotifJudul').textContent = sukses ? 'Berhasil' : 'Perhatian';
                document.getElementById('popupNotifPesan').textContent = item.pesan;

                var tombol = document.getElementById('popupNotifOk');
                tombol.className = 'popup-layar__tombol popup-ya ' + (sukses ? 'popup-ya-hijau' : 'popup-ya-merah');
                tombol.textContent = sukses ? 'Mengerti' : 'Tutup';

                buka('popupNotif');
                setTimeout(function () { tombol.focus(); }, 50);

                /* Pesan berhasil cukup ditahan sebentar; pesan gagal tetap
                   menunggu sampai benar-benar dibaca. */
                if (sukses) {
                    timerNotif = setTimeout(tutupNotif, 4000);
                }
            }

            function tutupNotif() {
                if (timerNotif) { clearTimeout(timerNotif); timerNotif = null; }
                tutup(document.getElementById('popupNotif'));
                notifAktif = false;
                /* Jeda sebentar supaya popup berikutnya tidak langsung menimpa
                   popup yang baru saja ditutup di layar yang sama. */
                setTimeout(tampilkanNotifBerikutnya, 180);
            }

            document.querySelectorAll('[data-tutup-notif]').forEach(function (b) {
                b.addEventListener('click', tutupNotif);
            });

            var konfirmasiTertunda = null;

            window.popupKonfirmasi = function (opsi) {
                return new Promise(function (resolve) {
                    document.getElementById('popupKonfirmasiJudul').textContent = opsi.judul || 'Lanjutkan?';
                    document.getElementById('popupKonfirmasiPesan').textContent = opsi.pesan || '';
                    document.getElementById('popupKonfirmasiIkon').className =
                        'popup-layar__ikon ' + (opsi.ikon || 'popup-ikon-indigo');

                    var ya = document.getElementById('popupKonfirmasiYa');
                    ya.textContent = opsi.tombolYa || 'Ya, Lanjutkan';
                    ya.className = 'popup-layar__tombol popup-ya ' + (opsi.tombol || '');

                    konfirmasiTertunda = resolve;
                    buka('popupKonfirmasi');
                    setTimeout(function () { ya.focus(); }, 50);
                });
            };

            function jawabKonfirmasi(jawab) {
                if (!konfirmasiTertunda) return;
                var resolve = konfirmasiTertunda;
                konfirmasiTertunda = null;
                tutup(document.getElementById('popupKonfirmasi'));
                resolve(jawab);
            }

            document.getElementById('popupKonfirmasiYa')
                .addEventListener('click', function () { jawabKonfirmasi(true); });
            document.querySelectorAll('[data-tutup-konfirmasi]').forEach(function (b) {
                b.addEventListener('click', function () { jawabKonfirmasi(false); });
            });

            /* Escape menutup apa pun yang sedang terbuka. Konfirmasi yang
               sedang unanswered dijawab "batal" supaya form yang memanggilnya
               tidak menggantung. */
            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Escape') return;
                if (konfirmasiTertunda) {
                    jawabKonfirmasi(false);
                    return;
                }
                document.querySelectorAll('.modal-kelas:not([hidden])').forEach(tutup);
            });

            window.konfirmasiHapus = function (form) {
                popupKonfirmasi({
                    judul: 'Hapus kelas ini?',
                    pesan: 'Kelas "' + (form.getAttribute('data-nama-kelas') || '') + '" akan dihapus. '
                        + 'Kelas yang masih punya Santri tidak akan dihapus.',
                    tombolYa: 'Ya, Hapus',
                    ikon: 'popup-ikon-merah',
                    tombol: 'popup-ya-merah'
                }).then(function (ya) {
                    /* form.submit() sengaja dipakai, bukan requestSubmit():
                       yang pertama tidak memicu onsubmit lagi sehingga popup ini
                       tidak muncul berulang kali. */
                    if (ya) form.submit();
                });
                return false;
            };

            window.konfirmasiTahun = function (form) {
                var dari = form.querySelector('[name=dari_tahun_ajar]').value;
                var ke = form.querySelector('[name=ke_tahun_ajar]').value;
                var pindah = form.querySelector('[name=pindahkan_santri]').checked;

                var pesan = 'Buat master kelas ' + dari + ' → ' + ke + '?';
                if (pindah) pesan += '\n\nSemua Santri akan dipindah ke tahun ajar baru (tingkat naik satu).';

                popupKonfirmasi({
                    judul: 'Buat tahun ajar baru?',
                    pesan: pesan,
                    tombolYa: 'Ya, Buat',
                    ikon: 'popup-ikon-hijau',
                    tombol: 'popup-ya-hijau'
                }).then(function (ya) {
                    if (ya) form.submit();
                });
                return false;
            };

            /* Notifikasi muncul setelah halaman selesai dirender supaya tidak
               melompat di tengah transisi. */
            setTimeout(tampilkanNotifBerikutnya, 250);

            /* ============================================================
               MODAL: Naik Kelas (pratinjau jumlah Santri)
               ============================================================ */
            var selectAsal = document.getElementById('dari_kelas_id');
            var selectTujuan = document.getElementById('ke_kelas_id');
            var info = document.getElementById('infoNaik');
            var tombol = document.getElementById('tombolNaik');
            var cache = {};

            function isiOpsiTujuan() {
                selectTujuan.innerHTML = '<option value="">— pilih kelas tujuan —</option>';

                var asal = selectAsal.options[selectAsal.selectedIndex];
                var jenisAsal = asal ? (asal.dataset.jenis || '') : '';

                // Hanya kelas sekelas sejenis yang ditawarkan: memindahkan kelas
                // pengajian ke kelas sekolah berarti menukar orang, bukan kelas.
                var grup = {};
                Array.prototype.forEach.call(selectAsal.options, function (o) {
                    if (!o.value) return;
                    if (o.dataset.jenis !== jenisAsal) return;
                    var ta = o.dataset.ta || '';
                    (grup[ta] = grup[ta] || []).push({ v: o.value, n: o.dataset.nama || o.text });
                });

                Object.keys(grup).forEach(function (ta) {
                    if (!grup[ta].length) return;
                    var g = document.createElement('optgroup');
                    g.label = ta;
                    grup[ta].forEach(function (o) {
                        if (o.v === selectAsal.value) return;
                        var el = document.createElement('option');
                        el.value = o.v;
                        el.text = o.n;
                        g.appendChild(el);
                    });
                    selectTujuan.appendChild(g);
                });

                selectTujuan.disabled = Object.keys(grup).length === 0;
            }

            window.pratinjauNaik = function () {
                isiOpsiTujuan();
                var id = selectAsal.value;
                if (!id) { info.hidden = true; tombol.disabled = true; return; }
                tombol.disabled = true;
                info.hidden = false;
                info.textContent = 'Menghitung Santri…';

                if (cache[id]) { tampilkan(cache[id]); return; }

                fetch('{{ route('kelas.pratinjau', ['kelas' => 0]) }}'.replace('/0/', '/' + id + '/'), {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(function (r) { return r.json(); })
                    .then(function (d) { cache[id] = d; tampilkan(d); })
                    .catch(function () {
                        info.textContent = 'Tidak bisa menghitung jumlah Santri. Periksa lagi sebelum melanjutkan.';
                    });
            };

            function tampilkan(d) {
                if (!d.jumlah) {
                    info.textContent = 'Kelas "' + d.kelas + '" (' + d.tahun_ajar + ') tidak punya Santri.';
                    tombol.disabled = true;
                    return;
                }
                tombol.disabled = false;

                if (d.jenis === 'pengajian') {
                    info.textContent = d.jumlah + ' Santri akan dipindah dari kelas pengajian "' + d.kelas + '" ('
                        + d.tahun_ajar + ')' + '. Kelas pengajian tidak naik otomatis — pindahkan yang memang naik saja.';
                    return;
                }

                info.textContent = d.jumlah + ' Santri akan dipindah dari "' + d.kelas + '" (' + d.tahun_ajar + ')'
                    + (d.berikutnya ? '. Saran kelas berikutnya: ' + d.berikutnya + '.' : '.');
            }

            if (selectAsal) { selectAsal.addEventListener('change', window.pratinjauNaik); }
            if (selectTujuan) {
                selectTujuan.addEventListener('change', function () {
                    tombol.disabled = !selectAsal.value || !selectTujuan.value;
                });
            }
        })();
    </script>
</body>
</html>
