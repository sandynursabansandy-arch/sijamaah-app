<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.pwa-head')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Santri — SANTRI.ID</title>
    @vite(['resources/css/app.css'])
    <style>
        /* Dua utility di bawah dipakai halaman ini tapi belum ada di build CSS,
           dan build CSS produksi tidak bisa di-refresh (file statisanya dilayani
           cache openresty). Ditulis inline agar tampilan sama di lokal & hosting.
           Lihat AGENTS.md bagian "Batasan File Build di Hosting". */
        .hover\:text-slate-700:hover { color: rgb(51 65 85); }
        .w-\[24\%\] { width: 24%; }

        /* Cegah layout shift saat scrollbar muncul/hilang selama animasi masuk */
        html {
            scrollbar-gutter: stable;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-in {
            animation: slideIn 0.5s ease-out both;
        }

        .animate-fade-in {
            animation: fadeIn 0.6s ease-in both;
        }

        .animate-slide-up {
            animation: slideUp 0.6s ease-out both;
        }

        .transition-smooth {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }

        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .status-active {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-inactive {
            background-color: #fee2e2;
            color: #7f1d1d;
        }

        .santri-scroll {
            max-height: 580px; overflow: auto; scrollbar-width: thin;
            scrollbar-color: #10b981 #e2e8f0;
        }
        .santri-scroll::-webkit-scrollbar { width: 10px; height: 10px; }
        .santri-scroll::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 9999px; }
        .santri-scroll::-webkit-scrollbar-thumb { background: #10b981; border-radius: 9999px; }
        .santri-scroll thead th {
            position: sticky; top: 0; z-index: 10;
            background: #10b981; color: #ffffff;
            box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.12);
        }
        .santri-scroll thead th:first-child {
            position: sticky; left: 0; z-index: 11;
        }
        .santri-scroll tbody td:first-child {
            position: sticky; left: 0; z-index: 5; background: #fff;
        }
        .santri-scroll tbody tr:nth-child(odd) td:first-child { background: #f9fafb; }
        .santri-scroll tbody tr:nth-child(even) td:first-child { background: #fff; }
        .santri-scroll tbody tr:hover td:first-child { background: #f1f5f9 !important; }
        .santri-scroll thead tr { background: transparent; }

        .alert-animate {
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .filter-gradient { background: #ffffff; border: 1px solid #e2e8f0; }
        .animate-fade-in-fast { animation: fadeInFast 0.15s ease-out both; }
        @keyframes fadeInFast { from { opacity: 0; transform: translateY(-6px) scale(0.97); } to { opacity: 1; transform: translateY(0) scale(1); } }

        .th-badge {
            display: inline-block;
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 6px;
            padding: 3px 12px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.02em;
            white-space: nowrap;
        }

        /* ===== Kartu kontak: ikon WhatsApp resmi + teks alamat ===== */
        .kontak {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            transition: background-color 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }
        .kontak:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
        }

        /* Ikon WhatsApp: gelembung hijau resmi #25D366 + simbol putih di dalamnya */
        .wa-ikon {
            flex: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: #25d366;
            color: #ffffff;
            text-decoration: none;
            transition: background-color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
        }
        .wa-ikon:hover {
            background: #1eb955;
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(37, 211, 102, 0.42);
        }
        .wa-ikon:active { transform: translateY(0); }
        .wa-ikon:focus-visible { outline: 2px solid #0f766e; outline-offset: 2px; }
        .wa-ikon svg { display: block; width: 26px; height: 26px; }

        .wa-ikon-kosong {
            background: #e2e8f0;
            color: #94a3b8;
            cursor: default;
        }
        .wa-ikon-kosong:hover {
            background: #e2e8f0;
            transform: none;
            box-shadow: none;
        }

        .kontak-teks { min-width: 0; }
        .kontak-label {
            display: block;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #94a3b8;
            line-height: 1.2;
        }
        .kontak-isi {
            display: block;
            font-size: 12px;
            line-height: 1.4;
            color: #334155;
            overflow-wrap: anywhere;
        }

        #toast-container { position: fixed; top: 24px; left: 50%; transform: translateX(-50%); z-index: 9999; pointer-events: none; }
        .toast {
            pointer-events: auto;
            display: flex; align-items: center; gap: 10px;
            padding: 14px 24px; border-radius: 12px;
            font-size: 14px; font-weight: 600; color: #fff;
            box-shadow: 0 8px 32px rgba(0,0,0,0.22);
            animation: toastIn 0.35s ease-out forwards;
            max-width: 420px;
        }
        .toast.success { background: #059669; }
        .toast.error   { background: #dc2626; }
        .toast.fade-out { animation: toastOut 0.3s ease-in forwards; }
        @keyframes toastIn { from { opacity:0; transform:translateY(-20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes toastOut { from { opacity:1; transform:translateY(0) scale(1); } to { opacity:0; transform:translateY(-20px) scale(0.95); } }
    </style>
</head>
<body class="bg-gradient-to-b from-slate-100 via-white to-emerald-50 text-slate-800 min-h-screen pb-8">                @include('partials.app-header')

    <div id="toast-container"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-5">
        <!-- Page Title -->
        <div class="mb-4 flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-400 to-purple-500 flex items-center justify-center shadow-md">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div>
                <h1 class="text-[15px] font-bold text-slate-800 leading-tight">Data Santri</h1>
                <p class="text-[11px] text-slate-400">Manajemen data santri aktif</p>
            </div>
        </div>

        <!-- Success Message (toast) -->
        @if(session('success'))
            <script>
                (function() {
                    var container = document.getElementById('toast-container');
                    if (!container) return;
                    var toast = document.createElement('div');
                    toast.className = 'toast success';
                    toast.innerHTML = '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ session('success') }}';
                    container.appendChild(toast);
                    setTimeout(function() { toast.classList.add('fade-out'); }, 2800);
                    setTimeout(function() { toast.remove(); }, 3100);
                })();
            </script>
        @endif

        <!-- Filter & Search Section -->
        <form method="GET" action="{{ route('santri.index') }}" class="filter-gradient rounded-xl p-3 mb-5 animate-fade-in shadow-sm">
            <div class="flex flex-wrap items-end gap-2.5">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Filter Santri</label>
                    <select name="filter" class="border-0 py-1.5 px-2.5 rounded-md focus:ring-2 focus:ring-emerald-300 text-slate-800 transition-smooth focus:outline-none text-[13px] bg-slate-50 h-[34px] box-border">
                        <option value="">Semua</option>
                        <option value="pengajar" {{ $filter == 'pengajar' ? 'selected' : '' }}>Pengajar</option>
                        <option value="santri" {{ $filter == 'santri' ? 'selected' : '' }}>Santri</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Filter Rayon</label>
                    <select name="kamar_id" class="border-0 py-1.5 px-2.5 rounded-md focus:ring-2 focus:ring-emerald-300 text-slate-800 transition-smooth focus:outline-none text-[13px] bg-slate-50 h-[34px] box-border">
                        <option value="">Semua Rayon</option>
                        @foreach($daftarKamar as $kamar)
                            <option value="{{ $kamar->id }}" {{ $kamarId == $kamar->id ? 'selected' : '' }}>
                                {{ $kamar->nama_kamar }}
                            </option>
                        @endforeach
                    </select>
                </div>
                {{-- Dua jalur kelas dipisah, bukan satu dropdown "Kelas": satu
                     Santri bisa punya kelas pengajian dan kelas sekolah sekaligus,
                     jadi satu filter tidak cukup untuk menjangkaunya. --}}
                @if($daftarKelas->isNotEmpty())
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Tahun Ajar</label>
                        <select name="tahun_ajar" class="border-0 py-1.5 px-2.5 rounded-md focus:ring-2 focus:ring-emerald-300 text-slate-800 transition-smooth focus:outline-none text-[13px] bg-slate-50 h-[34px] box-border">
                            @foreach($daftarTahunAjar as $ta)
                                <option value="{{ $ta }}" {{ $ta === $tahunAjar ? 'selected' : '' }}>{{ $ta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Kelas Pengajian</label>
                        <select name="kelas_pengajian_id" class="border-0 py-1.5 px-2.5 rounded-md focus:ring-2 focus:ring-emerald-300 text-slate-800 transition-smooth focus:outline-none text-[13px] bg-slate-50 h-[34px] box-border">
                            <option value="">Semua</option>
                            @foreach($daftarKelas->filter->isPengajian() as $k)
                                <option value="{{ $k->id }}" {{ (string) $k->id === $kelasPengajianId ? 'selected' : '' }}>{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Kelas Sekolah</label>
                        <select name="kelas_sekolah_id" class="border-0 py-1.5 px-2.5 rounded-md focus:ring-2 focus:ring-emerald-300 text-slate-800 transition-smooth focus:outline-none text-[13px] bg-slate-50 h-[34px] box-border">
                            <option value="">Semua</option>
                            @foreach($daftarKelas->reject->isPengajian() as $k)
                                <option value="{{ $k->id }}" {{ (string) $k->id === $kelasSekolahId ? 'selected' : '' }}>{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wide mb-1 text-slate-600 pl-0.5">Cari Santri</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Nama atau NIS..." 
                        class="w-full sm:w-48 border-0 py-1.5 px-2.5 rounded-md focus:ring-2 focus:ring-emerald-300 text-slate-800 transition-smooth focus:outline-none text-[13px] bg-slate-50 h-[34px] box-border">
                </div>
                <div class="ml-auto flex items-center gap-2">
                    @if($kelasPengajianId !== '' || $kelasSekolahId !== '')
                        <a href="{{ route('santri.index', array_filter(['kamar_id' => $kamarId, 'filter' => $filter, 'search' => $search])) }}"
                           class="text-[12px] font-semibold text-slate-500 hover:text-slate-700">Reset kelas</a>
                    @endif
                    <button type="submit" class="bg-emerald-500 text-white py-1.5 px-4 rounded-md font-semibold hover:bg-emerald-600 shadow transition-smooth text-[13px] h-[34px] box-border">
                        Cari
                    </button>
                </div>
            </div>
        </form>

        <!-- Add Button -->
        <div class="mb-5 flex items-center justify-between animate-slide-up">
            @if(auth()->user()?->canManagePresensi())
                <a href="{{ route('santri.create') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white px-5 py-2.5 rounded-lg font-semibold text-sm hover-lift shadow-lg transform transition-smooth">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Santri
                </a>
                <a href="{{ route('santri.import.form') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white px-5 py-2.5 rounded-lg font-semibold text-sm hover-lift shadow-lg transform transition-smooth">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Import CSV
                </a>
                <button type="button" id="cetakRekapBtn" onclick="openCetakRekapPopup()"
                    class="inline-flex items-center gap-2 bg-white text-slate-700 border border-slate-300 px-5 py-2.5 rounded-lg font-semibold text-sm hover:bg-slate-50 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9h.01M10 9h.01"/>
                    </svg>
                    <span class="hidden sm:inline">Cetak Rekap</span>
                </button>
             @endif
         </div>
 
        <!-- Santri Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in border border-slate-100">
            <div class="santri-scroll">
                    <table class="w-full min-w-[900px]">
                        <thead>
                            <tr class="bg-gradient-to-r from-emerald-500 to-emerald-600 text-white">
<th class="px-2 py-2.5 text-center text-[13px] font-bold w-[4%]"><span class="th-badge">No</span></th>
                                <th class="px-2 py-2.5 text-left w-[22%]"><span class="th-badge">Nama</span></th>
                                <th class="px-2 py-2.5 text-left w-[13%]"><span class="th-badge">Kamar</span></th>
                                <th class="px-2 py-2.5 text-left w-[13%]"><span class="th-badge">Kelas Pengajian</span></th>
                                <th class="px-2 py-2.5 text-left w-[10%]"><span class="th-badge">Kelas Sekolah</span></th>
                                <th class="px-2 py-2.5 text-center w-[9%]"><span class="th-badge">Jabatan</span></th>
                                <th class="px-2 py-2.5 text-left w-[24%]"><span class="th-badge">Kontak &amp; Alamat</span></th>
                                @if(auth()->user()?->canManagePresensi())
                                    <th class="px-2 py-2.5 text-center w-[10%]"><span class="th-badge">Aksi</span></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($santris as $index => $santri)
                                <tr class="hover:bg-slate-50 transition-smooth">
                                    <td class="px-2 py-2.5 text-center font-semibold text-slate-500 text-[13px]">{{ ($santris->currentPage() - 1) * 15 + $index + 1 }}</td>
                                <td class="px-3 py-2.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-400 to-blue-400 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                            {{ substr($santri->nama, 0, 1) }}
                                        </div>
                                        <p class="font-semibold text-slate-900 text-[13px]">{{ $santri->nama }}</p>
                                    </div>
                                </td>
                                <td class="px-3 py-2.5 text-slate-600 text-[13px]">{{ $santri->kamar->nama_kamar }}</td>
                                <td class="px-3 py-2.5 text-slate-600 text-[13px]">
                                    @if(trim((string) $santri->kelas_pengajian) !== '')
                                        <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 px-1.5 py-0.5 rounded-md text-[12px] font-semibold">{{ $santri->kelas_pengajian }}</span>
                                    @else
                                        <span class="text-slate-400 text-[12px]">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 text-slate-600 text-[13px]">
                                    @if(trim((string) $santri->kelas_sekolah) !== '')
                                        <span class="inline-block bg-indigo-50 text-indigo-700 px-1.5 py-0.5 rounded-md text-[12px] font-semibold">{{ $santri->kelas_sekolah }}</span>
                                    @else
                                        <span class="text-slate-400 text-[12px]">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 text-left text-[13px]">
                                    @if($santri->jabatan)
                                        <span class="inline-block px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-medium text-[12px]">{{ $santri->jabatan }}</span>
                                    @else
                                        <span class="text-slate-400 text-[12px]">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 text-[13px]">
                                    <div class="kontak">
                                        @if($santri->whatsapp_url)
                                            <a href="{{ $santri->whatsapp_url }}" target="_blank" rel="noopener"
                                                class="wa-ikon"
                                                title="Hubungi {{ $santri->nama }} via WhatsApp"
                                                aria-label="Hubungi {{ $santri->nama }} via WhatsApp">
                                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
                                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                                </svg>
                                            </a>
                                        @else
                                            <span class="wa-ikon wa-ikon-kosong" title="Nomor WhatsApp belum diisi" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="currentColor" focusable="false">
                                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                                </svg>
                                            </span>
                                        @endif
                                        <div class="kontak-teks">
                                            <span class="kontak-label">Alamat</span>
                                            <span class="kontak-isi">{{ $santri->alamat_singkat ?: 'Alamat belum diisi' }}</span>
                                        </div>
                                    </div>
                                </td>
                                @if(auth()->user()?->canManagePresensi())
                                    <td class="px-3 py-2.5 text-center">
                                        <div class="flex gap-1.5 justify-center">
                                            <a href="{{ route('santri.edit', $santri->id) }}"
                                                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-md text-[11px] font-semibold transition-smooth">
                                                Edit
                                            </a>
                                            <button type="button" onclick="showSantriDetail({{ $santri->id }})"
                                                class="w-8 h-8 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center transition-colors duration-200"
                                                title="Lihat Detail Lengkap">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </button>
                                            <form method="POST" action="{{ route('santri.destroy', $santri->id) }}" class="inline"
                                                    onsubmit="return confirm('Yakin ingin menghapus santri ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-md text-[11px] font-semibold transition-smooth">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                                </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()?->canManagePresensi() ? 7 : 6 }}" class="px-6 py-12 text-center">
                                    <h3 class="text-lg font-bold text-slate-800 mb-1">Tidak ada santri</h3>
                                    <p class="text-slate-500 text-sm mb-3">Belum ada data santri yang cocok dengan pencarian Anda.</p>
                                    @if(auth()->user()?->canManagePresensi())
                                        <a href="{{ route('santri.create') }}" class="inline-block bg-emerald-500 text-white px-5 py-2 rounded-lg font-semibold hover-lift text-sm">
                                            Tambah Santri Pertama
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($santris->hasPages())
                <div class="px-4 py-3 border-t border-slate-200 flex justify-center">
                    {{ $santris->links() }}
                </div>
            @endif
        </div>

        <!-- Info Cards -->
        <div class="mt-6 flex items-center gap-3 animate-fade-in max-w-lg mx-auto">
            <div class="flex-1 bg-white rounded-lg p-3 shadow-sm border border-slate-100 text-center">
                <p class="text-slate-500 text-[10px] font-semibold uppercase">Total Santri</p>
                <p class="text-xl font-bold text-emerald-600">{{ $santris->total() }}</p>
            </div>
            <div class="flex-1 bg-white rounded-lg p-3 shadow-sm border border-slate-100 text-center">
                <p class="text-slate-500 text-[10px] font-semibold uppercase">Total Kamar</p>
                <p class="text-xl font-bold text-blue-600">{{ count($daftarKamar) }}</p>
            </div>
            <div class="flex-1 bg-white rounded-lg p-3 shadow-sm border border-slate-100 text-center">
                <p class="text-slate-500 text-[10px] font-semibold uppercase">Halaman</p>
                <p class="text-xl font-bold text-purple-600">{{ $santris->currentPage() }} / {{ $santris->lastPage() }}</p>
            </div>
        </div>
     </div>
 
    @if(auth()->user()?->canManagePresensi())
    <div id="cetakRekapPopup" class="fixed inset-0 bg-black/30 hidden items-center justify-center z-[99999]">
        <div class="bg-white rounded-xl shadow-2xl border border-slate-200 min-w-[240px] max-h-[80vh] overflow-y-auto animate-fade-in transform-gpu">
            <div class="px-4 py-3 border-b border-slate-100 flex justify-between items-center">
                <h3 class="text-sm font-semibold text-slate-700">Cetak Rekap Santri</h3>
                <button type="button" onclick="closeCetakRekapPopup()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="py-1">
                <a href="{{ route('santri.cetak') }}"
                   class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9h.01M10 9h.01"/></svg>
                    Semua Santri
                </a>
                @foreach($daftarKamar as $kamar)
                    <a href="{{ route('santri.cetak') . '?kamar_id=' . $kamar->id }}"
                       class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9h.01M10 9h.01"/></svg>
                        {{ $kamar->nama_kamar }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    <script>
        function openCetakRekapPopup() {
            var popup = document.getElementById('cetakRekapPopup');
            if (!popup) return;
            popup.classList.remove('hidden');
            popup.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
        function closeCetakRekapPopup() {
            var popup = document.getElementById('cetakRekapPopup');
            if (!popup) return;
            popup.classList.add('hidden');
            popup.classList.remove('flex');
            document.body.style.overflow = '';
        }
        document.getElementById('cetakRekapPopup').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCetakRekapPopup();
            }
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                var popup = document.getElementById('cetakRekapPopup');
                if (popup && !popup.classList.contains('hidden')) {
                    closeCetakRekapPopup();
                }
            }
        });
    </script>
    @endif

    <div id="logoutModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[1010]">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-4 animate-slide-up">
            <div class="p-6 text-center">
                <div class="mx-auto w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-800 mb-2">Keluar dari web?</h3>
                <p class="text-sm text-slate-500 mb-6">Apakah Anda yakin ingin keluar dari aplikasi ini?</p>
                <div class="flex gap-3">
                    <button onclick="closeLogoutModal()" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-all duration-200">Batal</button>
                    <button onclick="confirmLogout()" class="flex-1 px-4 py-2.5 rounded-xl bg-red-500 text-white text-sm font-medium hover:bg-red-600 transition-all duration-200 shadow-md shadow-red-200">Ya, Keluar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Santri Modal -->
    <div id="detailModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[1010]">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 p-0 overflow-hidden animate-slide-up">
            <div class="bg-gradient-to-r from-emerald-500 to-blue-600 px-6 py-4 text-white flex items-center justify-between">
                <h3 class="text-lg font-semibold">Detail Santri</h3>
                <button type="button" onclick="closeDetailModal()" class="text-white/80 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div id="detailContent" class="p-6 space-y-4 text-sm">
                <div class="grid grid-cols-2 gap-4">
                    <div><span class="font-semibold text-slate-600">Nama</span><p id="det-nama" class="text-slate-800"></p></div>
                    <div><span class="font-semibold text-slate-600">Kamar</span><p id="det-kamar" class="text-slate-800"></p></div>
                    <div><span class="font-semibold text-slate-600">Kelas Pengajian</span><p id="det-kelas-pengajian" class="text-slate-800"></p></div>
                    <div><span class="font-semibold text-slate-600">Kelas Sekolah</span><p id="det-kelas-sekolah" class="text-slate-800"></p></div>
                    <div><span class="font-semibold text-slate-600">Jabatan</span><p id="det-jabatan" class="text-slate-800"></p></div>
                    <div><span class="font-semibold text-slate-600">Status Pengajar</span><p id="det-is-pengajar" class="text-slate-800"></p></div>
                    <div><span class="font-semibold text-slate-600">No. HP Wali</span><p id="det-hp-wali" class="text-slate-800"></p></div>
                    <div><span class="font-semibold text-slate-600">No. WhatsApp</span><p id="det-wa" class="text-slate-800"></p></div>
                </div>
                <div>
                    <span class="font-semibold text-slate-600">Alamat Lengkap</span>
                    <p id="det-alamat" class="text-slate-800 mt-1"></p>
                </div>
                <div>
                    <span class="font-semibold text-slate-600">Koordinat GPS</span>
                    <p id="det-gps" class="text-slate-800"></p>
                </div>
            </div>
        </div>
    </div>

     <script>
        var santriDetailData = @json($santriDetailData);

        function showSantriDetail(id) {
            var d = santriDetailData[id];
            if (!d) return;
            document.getElementById('det-nama').textContent = d.nama;
            document.getElementById('det-kamar').textContent = d.kamar;
            document.getElementById('det-kelas-pengajian').textContent = d.kelas_pengajian;
            document.getElementById('det-kelas-sekolah').textContent = d.kelas_sekolah;
            document.getElementById('det-jabatan').textContent = d.jabatan;
            document.getElementById('det-is-pengajar').textContent = d.is_pengajar;
            document.getElementById('det-hp-wali').textContent = d.no_hp_wali;
            document.getElementById('det-wa').textContent = d.no_whatsapp;
            document.getElementById('det-alamat').textContent = d.alamat;
            document.getElementById('det-gps').textContent = (d.latitude !== '-' || d.longitude !== '-') ? d.latitude + ', ' + d.longitude : '-';
            var modal = document.getElementById('detailModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
        function closeDetailModal() {
            var modal = document.getElementById('detailModal');
            modal.style.transition = 'opacity 0.3s ease';
            modal.style.opacity = '0';
            setTimeout(function() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.style.opacity = '';
                modal.style.transition = '';
                document.body.style.overflow = '';
            }, 300);
        }
        document.addEventListener('click', function(e) {
            var modal = document.getElementById('detailModal');
            var detailBtn = e.target.closest('[onclick^="showSantriDetail"]');
            if (!detailBtn && !modal.contains(e.target)) {
                closeDetailModal();
            }
        });
    </script>

    <script>
        function toggleDropdown(id) {
            const el = document.getElementById(id);
            const menu = el.querySelector('.dropdown-menu');
            const wasHidden = menu.classList.contains('hidden');
            document.querySelectorAll('.dropdown-menu').forEach(d => d.classList.add('hidden'));
            if (wasHidden) menu.classList.remove('hidden');
        }
        document.addEventListener('click', function(e) {
            if (!e.target.closest('[id^="settings"]')) {
                document.querySelectorAll('.dropdown-menu').forEach(d => d.classList.add('hidden'));
            }
        });
    </script>

    <script>
        function showLogoutModal() {
            var modal = document.getElementById('logoutModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
        function closeLogoutModal() {
            var modal = document.getElementById('logoutModal');
            modal.style.transition = 'opacity 0.3s ease';
            modal.style.opacity = '0';
            setTimeout(function() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.style.opacity = '';
                modal.style.transition = '';
                document.body.style.overflow = '';
            }, 300);
        }
        function confirmLogout() {
            document.getElementById('logoutForm').submit();
        }
    </script>
</body>
</html>
